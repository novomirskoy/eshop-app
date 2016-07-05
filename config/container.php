<?php

use Novomirskoy\Websocket\Pusher\ServerPushHandlerRegistry;
use Novomirskoy\Websocket\Server\App\Registry\OriginRegistry;
use Novomirskoy\Websocket\Server\App\Registry\PeriodicRegistry;
use Novomirskoy\Websocket\Server\App\Registry\ServerRegistry;
use Novomirskoy\Websocket\Server\App\WampApplication;
use Novomirskoy\Websocket\Server\EntryPoint;
use Novomirskoy\Websocket\Server\Type\WebSocketServer;
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
    $eventDispatcher = $container->get(EventDispatcherInterface::class);
    $logger = $container->get(LoggerInterface::class);
    
    $application = new WampApplication(
        $eventDispatcher,
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