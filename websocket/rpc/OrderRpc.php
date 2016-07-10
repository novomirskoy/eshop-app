<?php

namespace app\websocket\rpc;

use app\services\MailerService;
use Novomirskoy\Websocket\Router\WampRequest;
use Novomirskoy\Websocket\RPC\RpcInterface;
use Ratchet\ConnectionInterface;

/**
 * Class OrderRpc
 * @package app\websocket\rpc
 */
class OrderRpc implements RpcInterface
{
    /**
     * @var MailerService
     */
    protected $mailerService;


    /**
     * OrderRpc constructor.
     *
     * @param MailerService $mailerService
     */
    public function __construct(MailerService $mailerService)
    {
        $this->mailerService = $mailerService;
    }

    /**
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param array $params
     *
     * @return array
     */
    public function formation(ConnectionInterface $connection, WampRequest $request, $params)
    {
        if (!array_key_exists('cart', $params)) {
            throw new \RuntimeException('Параметр cart не передан');
        }

        if (!array_key_exists('email', $params)) {
            throw new \RuntimeException('Параметр email не передан');
        }

        $this->mailerService->orderNotification($params['email'], $params['cart']);

        return ['result' => true];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'order.rpc';
    }
}
