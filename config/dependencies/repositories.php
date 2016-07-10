<?php

use app\repositories\ActiveRecordProductRepository;
use app\repositories\ActiveRecordUserRepository;
use app\repositories\ProductRepositoryInterface;
use app\repositories\UserRepositoryInterface;

$container = \Yii::$container;

$container->set(ProductRepositoryInterface::class, function () {
    return new ActiveRecordProductRepository();
});

$container->set(UserRepositoryInterface::class, function () {
    return new ActiveRecordUserRepository();
});