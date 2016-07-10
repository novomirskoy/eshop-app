<?php

namespace app\services;

/**
 * Class UserService
 * @package app\services
 */
class UserService
{
    /**
     * @return string
     */
    public function generateToken()
    {
        return (string)\Ramsey\Uuid\Uuid::uuid4();
    }
}
