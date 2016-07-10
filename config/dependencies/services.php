<?php

/** @var \yii\di\Container $container */
use app\services\UserService;

$container = \Yii::$container;

$container->set(UserService::class, function () {
    return new UserService();
});