<?php

namespace app\websocket\rpc;

use Novomirskoy\Websocket\Router\WampRequest;
use Novomirskoy\Websocket\RPC\RpcInterface;
use Ratchet\ConnectionInterface;
use yii\swiftmailer\Mailer;

/**
 * Class OrderRpc
 * @package app\websocket\rpc
 */
class OrderRpc implements RpcInterface
{
    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * OrderRpc constructor.
     *
     * @param Mailer $mailer
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param array $params
     */
    public function formation(ConnectionInterface $connection, WampRequest $request, $params)
    {
        if (!array_key_exists('items', $params)) {
            throw new \RuntimeException('Параметр items не передан');
        }

        $this
            ->mailer
            ->compose()
            ->setTo('to@domain.com')
            ->setSubject('Message subject')
            ->setTextBody('Plain text content')
            ->setHtmlBody('<b>HTML content</b>')
            ->send();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'order.rpc';
    }
}
