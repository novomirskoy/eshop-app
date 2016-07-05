<?php

use Novomirskoy\Websocket\Server\Type\WebSocketServer;

return [
    'debug' => true,
    'clientStorage' => [
        'ttl' => 60 * 60,
    ],
    'servers' => [
        WebSocketServer::class,
    ],    
];
