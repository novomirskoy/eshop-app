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
     * @param string $host
     * @param string $port
     */
    public function actionIndex($host, $port)
    {
        $container = \Yii::$container;

        $entryPoint = $container->get(EntryPoint::class);
        $entryPoint->launch(null, $host, $port, false);
    }
}
