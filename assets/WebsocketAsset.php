<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Class WebsocketAsset
 * @package app\assets
 */
class WebsocketAsset extends AssetBundle
{
    public $js = [
        'js/autobahn.min.js',
        'js/gos_web_socket_client.js',
    ];
}
