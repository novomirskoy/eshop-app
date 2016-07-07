<?php

use app\repositories\ActiveRecordProductRepository;
use app\repositories\ProductRepositoryInterface;

$container = \Yii::$container;

$container->set(ProductRepositoryInterface::class, function () {
    return new ActiveRecordProductRepository();
});