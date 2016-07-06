<?php

namespace app\websocket\rpc;

use Novomirskoy\Websocket\Router\WampRequest;
use Novomirskoy\Websocket\RPC\RpcInterface;
use Ratchet\ConnectionInterface;

/**
 * Class AcmeRpc
 * @package app\websocket\rpc
 */
class AcmeRpc implements RpcInterface
{
    /**
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param $params
     * 
     * @return array
     */
    public function sum(ConnectionInterface $connection, WampRequest $request, $params)
    {
        return ['result' => array_sum($params)];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'acme.rpc';
    }
}
