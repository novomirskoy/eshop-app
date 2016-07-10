<?php

namespace app\models;

/**
 * Class ShopUser
 * @package app\models
 *
 * @property integer $id
 * @property string $username
 * @property string $password
 * @property string $auth_key
 * @property string $accessToken
 */
class ShopUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'string'],
            [['password'], 'string'],
            [['auth_key'], 'string'],
            [['accessToken'], 'string'],
        ];
    }
}
