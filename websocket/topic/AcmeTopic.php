<?php

namespace app\websocket\topic;

use Novomirskoy\Websocket\Router\WampRequest;
use Novomirskoy\Websocket\Topic\TopicInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampConnection;

/**
 * Class AcmeTopic
 * @package app\websocket\topic
 */
class AcmeTopic implements TopicInterface
{
    /**
     * @param ConnectionInterface|WampConnection $connection
     * @param Topic $topic
     * @param WampRequest $request
     * 
     * @return void
     */
    public function onSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        $topic->broadcast(['msg' => $connection->resourceId . ' has joined ' . $topic->getId()]);
    }

    /**
     * @param ConnectionInterface|WampConnection $connection
     * @param Topic $topic
     * @param WampRequest $request
     * 
     * @return void
     */
    public function onUnSubscribe(ConnectionInterface $connection, Topic $topic, WampRequest $request)
    {
        $topic->broadcast(['msg' => $connection->resourceId . ' has left ' . $topic->getId()]);
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
        $topic->broadcast(['msg' => $event]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'acme.topic';
    }
}
