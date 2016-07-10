<?php

/** @var \yii\di\Container $container */
use app\services\MailerService;
use app\services\UserService;

$container = \Yii::$container;

$container->set(UserService::class, function () {
    return new UserService();
});

$container->set(MailerService::class, function () {
    /** @var \yii\swiftmailer\Mailer $mailer */
    $mailer = \Yii::$app->mailer;
    $from = 'endin.artyom@yandex.ru';

    return new MailerService($mailer, $from);
});