<?php

namespace app\commands;
use Novomirskoy\Websocket\Server\EntryPoint;
use yii\console\Controller;

/**
 * Class WebsocketServerController
 * @package app\commands
 */
class WebsocketServerController extends Controller
{
    /**
     * @param string $serverName
     * @param string $host
     * @param string $port
     */
    public function actionIndex($serverName, $host, $port)
    {
        $container = \Yii::$container;

        $entryPoint = $container->get(EntryPoint::class);
        $entryPoint->launch($serverName, $host, $port, false);
    }
}
