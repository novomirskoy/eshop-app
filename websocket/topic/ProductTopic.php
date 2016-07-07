<?php

namespace app\websocket\topic;

use app\repositories\ProductRepositoryInterface;
use Novomirskoy\Websocket\Router\WampRequest;
use Novomirskoy\Websocket\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

/**
 * Class ProductTopic
 * @package app\websocket\topic
 */
class ProductTopic implements TopicInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;

    /**
     * ProductTopic constructor.
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // TODO: Implement onSubscribe() method.
    }

    /**
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        // TODO: Implement onUnSubscribe() method.
    }

    /**
     * @param ConnectionInterface $connection
     * @param Topic $topic
     * @param WampRequest $request
     * @param string $event
     * @param array $exclude
     * @param array $eligible
     */
    public function onPublish(ConnectionInterface $connection, Topic $topic, WampRequest $request, $event, array $exclude, array $eligible)
    {
        // TODO: Implement onPublish() method.
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'product.topic';
    }
}
