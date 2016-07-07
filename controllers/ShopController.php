<?php

namespace app\controllers;

use yii\web\Controller;

/**
 * Class ShopController
 * @package app\controllers
 */
class ShopController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');    
    }    
}
