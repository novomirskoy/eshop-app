<?php

namespace app\websocket\rpc;

use app\models\ShopUser;
use app\repositories\exception\UserNotFoundException;
use app\repositories\UserRepositoryInterface;
use Novomirskoy\Websocket\Router\WampRequest;
use Novomirskoy\Websocket\RPC\RpcInterface;
use Ratchet\ConnectionInterface;

/**
 * Class UserRpc
 * @package app\websocket\rpc
 */
class UserRpc implements RpcInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $repository;

    /**
     * UserRpc constructor.
     *
     * @param UserRepositoryInterface $repository
     */
    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param array $params
     *
     * @return array
     */
    public function login(ConnectionInterface $connection, WampRequest $request, $params)
    {
        if (!array_key_exists('creds', $params)) {
            throw new \RuntimeException('Параметр creds не передан');
        }

        $email = $params['creds']['email'];
        $password = $params['creds']['password'];

        $user = $this->getUserByUsername($email);

        if (!password_verify($password, $user->password)) {
            throw new \RuntimeException('Неверный пароль');
        }

        return ['token' => $user->accessToken];
    }

    /**
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param array $params
     *
     * @return array
     */
    public function register(ConnectionInterface $connection, WampRequest $request, $params)
    {
        if (!array_key_exists('userInfo', $params)) {
            throw new \RuntimeException('Параметр userInfo не передан');
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param $params
     *
     * @return array
     */
    public function info(ConnectionInterface $connection, WampRequest $request, $params)
    {
        if (!array_key_exists('token', $params)) {
            throw new \RuntimeException('Token не передан');
        }

        return [
            'user' => [
                'id' => '1',
                'name' => 'test',
                'email' => 'test@test.ru',
            ],
        ];
    }

    /**
     * @param string $userName
     *
     * @return ShopUser
     *
     * @throws UserNotFoundException
     */
    protected function getUserByUsername($userName)
    {
        $criteria = ['username' => $userName];

        $user = $this
            ->repository
            ->findOneBy($criteria);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        return $user;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user.rpc';
    }
}
