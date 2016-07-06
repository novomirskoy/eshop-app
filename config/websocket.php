<?php

use Novomirskoy\Websocket\Server\Type\WebSocketServer;
use app\websocket\topic;
use app\websocket\rpc;

return [
    'debug' => true,
    'clientStorage' => [
        'ttl' => 60 * 60,
    ],
    'servers' => [
        WebSocketServer::class,
    ],
    'rpc' => [
        rpc\AcmeRpc::class,
    ],
    'topics' => [
        topic\AcmeTopic::class,
    ],
    'pubSubRouter' => [
        'cacheDir' => __DIR__ . '/../runtime/pubSubRouter/cache',
        'routers' => [
            'websocket' => [
                'context' => [
                    'tokenSeparator' => '/',
                ],
                'resources' => [
                    __DIR__ . '/websocket/pubsub/routing.yml',
                ],
                'loaders' => [
                    'pubsub_router.yaml.loader',
                ],
            ],
        ],
    ],
];
