<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Novomirskoy\Websocket\Client\Auth\WebsocketAuthenticationProvider;
use Novomirskoy\Websocket\Client\ClientStorage;
use Novomirskoy\Websocket\Client\ClientStorageInterface;
use Novomirskoy\Websocket\Event\ClientEventListener;
use Novomirskoy\Websocket\PubSubRouter\Cache\PhpFileCacheDecorator;
use Novomirskoy\Websocket\PubSubRouter\Generator\Generator;
use Novomirskoy\Websocket\PubSubRouter\Generator\GeneratorInterface;
use Novomirskoy\Websocket\PubSubRouter\Loader\RouteLoader;
use Novomirskoy\Websocket\PubSubRouter\Loader\YamlFileLoader;
use Novomirskoy\Websocket\PubSubRouter\Matcher\Matcher;
use Novomirskoy\Websocket\PubSubRouter\Matcher\MatcherInterface;
use Novomirskoy\Websocket\PubSubRouter\Router\RouteCollection;
use Novomirskoy\Websocket\PubSubRouter\Router\Router;
use Novomirskoy\Websocket\PubSubRouter\Router\RouterContext;
use Novomirskoy\Websocket\PubSubRouter\Tokenizer\Tokenizer;
use Novomirskoy\Websocket\PubSubRouter\Tokenizer\TokenizerInterface;
use Novomirskoy\Websocket\Pusher\PusherRegistry;
use Novomirskoy\Websocket\Pusher\ServerPushHandlerRegistry;
use Novomirskoy\Websocket\Router\NullPubSubRouter;
use Novomirskoy\Websocket\Router\WampRouter;
use Novomirskoy\Websocket\RPC\RpcInterface;
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
use Novomirskoy\Websocket\Topic\TopicInterface;
use Novomirskoy\Websocket\Topic\TopicPeriodicTimer;
use Psr\Log\LoggerInterface;
use Ratchet\Wamp\TopicManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use yii\di\Container;

$container = \Yii::$container;
$websocketConfig = require_once __DIR__ . '/websocket.php';
require_once __DIR__ . '/dependencies/repositories.php';

$container->set(EntryPoint::class, function (Container $container) {
    $serverRegistry = $container->get(ServerRegistry::class);

    return new EntryPoint($serverRegistry);
});
$container->set('web_socket.entry_point', EntryPoint::class);

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

$container->set(WampApplication::class, function (Container $container) {
    $rpcDispatcher = $container->get(RpcDispatcherInterface::class);
    $topicDispatcher = $container->get(TopicDispatcherInterface::class);
    /** @var EventDispatcherInterface $eventDispatcher */
    $eventDispatcher = $container->get('web_socket.event_dispatcher');
    $clientStorage = $container->get(ClientStorageInterface::class);
    $wampRouter = $container->get(WampRouter::class);
    $logger = $container->get(LoggerInterface::class);
    
    $application = new WampApplication(
        $rpcDispatcher,
        $topicDispatcher,
        $eventDispatcher,
        $clientStorage,
        $wampRouter,
        $logger
    );
    
    return $application;
});

$container->set(TopicPeriodicTimer::class, function (Container $container) {
    /** @var \React\EventLoop\LoopInterface $loop */
    $loop = $container->get('web_socket.server.event_loop');

    return new TopicPeriodicTimer($loop);
});

$container->set(ClientStorageInterface::class, function (Container $container) use ($websocketConfig) {
    $ttl = array_key_exists('ttl', $websocketConfig['clientStorage'])
        ? $websocketConfig['clientStorage']['ttl']
        : PHP_INT_MAX;
    $logger = $container->get(LoggerInterface::class);

    return new ClientStorage($ttl, $logger);
});

/*
 * Registry
 */

$container->set(ServerRegistry::class, function (Container $container) use ($websocketConfig) {
    $registry = new ServerRegistry();

    $servers = $websocketConfig['servers'];
    foreach ($servers as $server) {
        $registry->addServer($container->get($server));
    }

    return $registry;
});
$container->set('web_socket.server.registry', ServerRegistry::class);

$container->set(RpcRegistry::class, function (Container $container) use ($websocketConfig) {
    $registry = new RpcRegistry();

    $rpcHandlers = $websocketConfig['rpc'] ?: [];
    foreach ($rpcHandlers as $handler) {
        /** @var RpcInterface $handlerService */
        $handlerService = $container->get($handler);
        $registry->addRpc($handlerService);
    }

    return $registry;
});
$container->set('web_socket.rpc.registry', RpcRegistry::class);

$container->set(TopicRegistry::class, function (Container $container) use ($websocketConfig) {
    $registry = new TopicRegistry();

    $topicHandlers = $websocketConfig['topics'] ?: [];
    foreach ($topicHandlers as $handler) {
        /** @var TopicInterface $handlerService */
        $handlerService = $container->get($handler);
        $registry->addTopic($handlerService);
    }

    return $registry;
});
$container->set('web_socket.topic.registry', TopicRegistry::class);

$container->set(PeriodicRegistry::class, function () {
    return new PeriodicRegistry();
});
$container->set('web_socket.periodic.registry', PeriodicRegistry::class);

$container->set(OriginRegistry::class, function () {
    return new OriginRegistry();
});
$container->set('web_socket.origins.registry', OriginRegistry::class);

$container->set(ServerPushHandlerRegistry::class, function () {
    return new ServerPushHandlerRegistry();
});
$container->set('web_socket.server_push_handler.registry', ServerPushHandlerRegistry::class);

$container->set(PusherRegistry::class, function () {
    return new PusherRegistry();
});
$container->set('web_socket.pusher_registry', PusherRegistry::class);

/*
 * Dispatcher
 */

$container->set(RpcDispatcherInterface::class, function (Container $container) {
    $registry = $container->get(RpcRegistry::class);

    return new RpcDispatcher($registry);
});
$container->set('web_socket.rpc.dispatcher', RpcDispatcherInterface::class);

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
$container->set('web_socket.topic.dispatcher', TopicDispatcherInterface::class);

/*
 * Manager
 */

$container->set(TopicManager::class, function () {
    return new TopicManager();
});
$container->set('web_socket.wamp.topic_manager', TopicManager::class);

/*
 * Router
 */

$container->set(NullPubSubRouter::class, function () {
    return new NullPubSubRouter();
});
$container->set('web_socket.null.pubsub.router', NullPubSubRouter::class);

$container->set(WampRouter::class, function (Container $container) use ($websocketConfig) {
    /** @var Router $router */
    $router = $container->get('pubsub_router.websocket');
    $logger = $container->get(LoggerInterface::class);

    return new WampRouter(
        $router,
        $websocketConfig['debug'] ?: false,
        $logger
    );
});
$container->set('web_socket.router.wamp', WampRouter::class);

/*
 * Pub-Sub router
 */
$container->set(TokenizerInterface::class, function () {
    return new Tokenizer();
});
$container->set('pubsub_router.tokenizer.default', TokenizerInterface::class);

$container->set(MatcherInterface::class, function (Container $container) {
    $tokenizer = $container->get(TokenizerInterface::class);
    
    return new Matcher($tokenizer);
});
$container->set('pubsub_router.matcher', MatcherInterface::class);

$container->set(GeneratorInterface::class, function (Container $container) {
    $tokenizer = $container->get(TokenizerInterface::class);
    
    return new Generator($tokenizer);
});
$container->set('pubsub_router.generator', GeneratorInterface::class);

$container->set('pubsub_router.php_file.cache', function () use ($websocketConfig) {
    $cacheDir = $websocketConfig['pubSubRouter']['cacheDir'];
    
    return new PhpFileCacheDecorator($cacheDir, true);
});

$container->set('pubsub_router.yaml.loader', function () {
    $fileLocator = new FileLocator();
    
    return new YamlFileLoader($fileLocator);
});

/*
 * Logger
 */

$container->set(LoggerInterface::class, function () {
    $log = new Logger('websocket');
    $log->pushHandler(new StreamHandler(__DIR__ . '/../runtime/logger.log', Logger::DEBUG));
    
    return $log;
});
$container->set('logger.websocket', LoggerInterface::class);

/*
 * EventManager
 */

$container->set(EventDispatcher::class, function () {
    return new EventDispatcher();
});
$container->set('web_socket.event_dispatcher', EventDispatcher::class);

/*
 * Events
 */

$container->set(ClientEventListener::class, function (Container $container) {
    $clientStorage = $container->get(ClientStorageInterface::class);
    $logger = $container->get(LoggerInterface::class);
});

/*
 * Authentication
 */

$container->set(WebsocketAuthenticationProvider::class, function(Container $container) {
    $tokenStorage = 'ThisTokenIsNotSoSecretChangeIt';
    $clientStorage = $container->get(ClientStorageInterface::class);
    $logger = $container->get(LoggerInterface::class);
});
$container->set('web_socket.websocket_authentification.provider', WebsocketAuthenticationProvider::class);

/*
 * Loop
 */

$container->set('web_socket.server.event_loop', function () {
    return React\EventLoop\Factory::create();
});

// Configure routers
$pubSubRouter = $websocketConfig['pubSubRouter'];
$routers = $pubSubRouter['routers'];

foreach ($routers as $name => $routerConf) {
    // RouteCollection
    $collectionServiceName = 'pubsub_router.collection.' . $name;
    $container->set($collectionServiceName, function() {
        return new RouteCollection();
    });
    /** @var RouteCollection $routeCollection */
    $routeCollection = $container->get($collectionServiceName);
    
    // Matcher
    $matcher = $container->get(MatcherInterface::class);
    $matcher->setCollection($routeCollection);
    
    // Generator
    $generator = $container->get(GeneratorInterface::class);
    $generator->setCollection($routeCollection);
    
    // RouteLoader
    $routeLoaderServiceName = 'pubsub_router.loader.' . $name;
    $routeLoader = new RouteLoader(
        $routeCollection,
        $container->get('pubsub_router.php_file.cache'), 
        $name
    );
    
    foreach ($routerConf['resources'] as $resource) {
        $routeLoader->addResource($resource);
    }
    
    foreach ($routerConf['loaders'] as $loader) {
        $routeLoader->addLoader($container->get($loader));
    }
    
    $container->set($routeLoaderServiceName, $routeLoader);
    
    // Router Context
    $contextConf = $routerConf['context'];
    $routerContextServiceName = 'pubsub_router.context.' . $name;
    $routerContext = new RouterContext();
    $routerContext->setTokenSeparator($contextConf['tokenSeparator']);
    
    $container->set($routerContextServiceName, $routerContext);
    
    // Router
    $routerServiceName = 'pubsub_router.' . $name;
    $router = new Router(
        $routeCollection,
        $matcher,
        $generator,
        $routeLoader,
        $name
    );
    
    $router->setContext($routerContext);
    
    $container->set($routerServiceName, $router);
    
    $routeLoader->load();
}
