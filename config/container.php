<?php

use Novomirskoy\Websocket\Client\ClientStorage;
use Novomirskoy\Websocket\Client\ClientStorageInterface;
use Novomirskoy\Websocket\Pusher\ServerPushHandlerRegistry;
use Novomirskoy\Websocket\Router\NullPubSubRouter;
use Novomirskoy\Websocket\Router\WampRouter;
use Novomirskoy\Websocket\Server\App\Dispatcher\RpcDispatcher;
use Novomirskoy\Websocket\Server\App\Dispatcher\RpcDispatcherInterface;
use Novomirskoy\Websocket\Server\App\Dispatcher\TopicDispatcher;
use Novomirskoy\Websocket\Server\App\Dispatcher\TopicDispatcherInterface;
use Novomirskoy\Websocket\Server\App\Registry\OriginRegistry;
use Novomirskoy\Websocket\Server\App\Registry\PeriodicRegistry;
use Novomirskoy\Websocket\Server\App\Registry\RpcRegistry;
use Novomirskoy\Websocket\Server\App\Registry\ServerRegistry;
use Novomirskoy\Websocket\Server\App\Registry\TopicRegistry;
use Novomirskoy\Websocket\Server\App\WampApplication;
use Novomirskoy\Websocket\Server\EntryPoint;
use Novomirskoy\Websocket\Server\Type\WebSocketServer;
use Novomirskoy\Websocket\Topic\TopicPeriodicTimer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Ratchet\Wamp\TopicManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use yii\di\Container;

$container = \Yii::$container;
$websocketConfig = require_once __DIR__ . '/websocket.php';

$container->set(EntryPoint::class, function ($container) {
    $serverRegistry = $container->get(ServerRegistry::class);

    return new EntryPoint($serverRegistry);
});

$container->set(ServerRegistry::class, function ($container) use ($websocketConfig) {
    $registry = new ServerRegistry();

    $servers = $websocketConfig['servers'];
    foreach ($servers as $server) {
        $registry->addServer($container->get($server));
    }

    return $registry;
});

$container->set(WebSocketServer::class, function (Container $container) {
    /** @var \React\EventLoop\LoopInterface $loop */
    $loop = $container->get('web_socket.server.event_loop');
    /** @var EventDispatcherInterface $eventDispatcher */
    $eventDispatcher = $container->get('web_socket.event_dispatcher');
    $periodicRegistry = $container->get(PeriodicRegistry::class);
    $wampApplication = $container->get(WampApplication::class);
    $originRegistry = $container->get(OriginRegistry::class);
    $originCheck = false;
    $topicManager = $container->get(TopicManager::class);
    $serverPushHandlerRegistry = $container->get(ServerPushHandlerRegistry::class);
    $logger = $container->get(LoggerInterface::class);
    
    return new WebSocketServer(
        $loop,
        $eventDispatcher,
        $periodicRegistry,
        $wampApplication,
        $originRegistry,
        $originCheck,
        $topicManager,
        $serverPushHandlerRegistry,
        $logger
    );
});

$container->set('web_socket.server.event_loop', function () {
    return React\EventLoop\Factory::create();
});

$container->set('web_socket.event_dispatcher', function () {
    return new Symfony\Component\EventDispatcher\EventDispatcher();
});

$container->set(PeriodicRegistry::class, function () {
    return new PeriodicRegistry();
});

$container->set(WampApplication::class, function (Container $container) {
    //@todo Добавить зависимости в приложение
    $rpcDispatcher = $container->get(RpcDispatcherInterface::class);
    $topicDispatcher = $container->get(TopicDispatcherInterface::class);
    /** @var EventDispatcherInterface $eventDispatcher */
    $eventDispatcher = $container->get('web_socket.event_dispatcher');
    $clientStorage = $container->get(ClientStorageInterface::class);
    $logger = $container->get(LoggerInterface::class);
    
    $application = new WampApplication(
        $rpcDispatcher,
        $topicDispatcher,
        $eventDispatcher,
        $clientStorage,
        $logger
    );
    
    return $application;
});

$container->set(OriginRegistry::class, function () {
    return new OriginRegistry();
});

$container->set(TopicManager::class, function () {
    return new TopicManager();
});

$container->set(ServerPushHandlerRegistry::class, function () {
    return new ServerPushHandlerRegistry();
});

$container->set(LoggerInterface::class, function () {
    return new NullLogger();
});

$container->set(RpcDispatcherInterface::class, function (Container $container) {
    $registry = $container->get(RpcRegistry::class);

    return new RpcDispatcher($registry);
});

$container->set(RpcRegistry::class, function () {
    return new RpcRegistry();
});

$container->set(TopicDispatcherInterface::class, function (Container $container) use ($websocketConfig) {
    $registry = $container->get(TopicRegistry::class);
    $wampRouter = $container->get(WampRouter::class);
    $topicPeriodicTimer = $container->get(TopicPeriodicTimer::class);
    $topicManager = $container->get(TopicManager::class);
    $logger = $container->get(LoggerInterface::class);

    return new TopicDispatcher(
        $registry,
        $wampRouter,
        $topicPeriodicTimer,
        $topicManager,
        $logger
    );
});

$container->set(TopicRegistry::class, function () {
    return new TopicRegistry();
});

$container->set(WampRouter::class, function (Container $container) use ($websocketConfig) {
    /** @var NullPubSubRouter $router */
    $router = $container->get('web_socket.null.pubsub.router');
    $logger = $container->get(LoggerInterface::class);

    return new WampRouter(
        $router,
        $websocketConfig['debug'] ?: false,
        $logger
    );
});

$container->set('web_socket.null.pubsub.router', function () {
    return new NullPubSubRouter();
});

$container->set(TopicPeriodicTimer::class, function (Container $container) {
    /** @var \React\EventLoop\LoopInterface $loop */
    $loop = $container->get('web_socket.server.event_loop');

    return new TopicPeriodicTimer($loop);
});

$container->set(TopicManager::class, function () {
    return new TopicManager();
});

$container->set(ClientStorageInterface::class, function (Container $container) use ($websocketConfig) {
    $ttl = array_key_exists('ttl', $websocketConfig['clientStorage'])
        ? $websocketConfig['clientStorage']['ttl']
        : PHP_INT_MAX;
    $logger = $container->get(LoggerInterface::class);

    return new ClientStorage($ttl, $logger);
});