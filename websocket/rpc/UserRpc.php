<?php

namespace app\websocket\rpc;

use app\models\ShopUser;
use app\repositories\exception\UserNotFoundException;
use app\repositories\UserRepositoryInterface;
use app\services\UserService;
use Novomirskoy\Websocket\Router\WampRequest;
use Novomirskoy\Websocket\RPC\RpcInterface;
use Ratchet\ConnectionInterface;
use yii\db\Transaction;

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
     * @var UserService
     */
    protected $service;

    /**
     * UserRpc constructor.
     *
     * @param UserRepositoryInterface $repository
     * @param UserService $service
     */
    public function __construct(UserRepositoryInterface $repository, UserService $service)
    {
        $this->repository = $repository;
        $this->service = $service;
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

        $email = $params['userInfo']['email'];
        $password = $params['userInfo']['password'];

        if ($this->userExist($email)) {
            throw new \RuntimeException('Пользователь уже существует');
        }

        /** @var \yii\db\Connection $db */
        $db = \Yii::$app->db;

        /** @var Transaction $transaction */
        $transaction = $db->beginTransaction();

        try {
            $user = new ShopUser();
            $user->username = $email;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->accessToken = $this->service->generateToken();
            $user->auth_key = $email;
            $user->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw new \RuntimeException('Невозможно создать пользователя', 500, $e);
        }

        return ['token' => $user->accessToken];
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

        $user = $this->getUserByToken($params['token']);

        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->username,
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
     * @param string $token
     *
     * @return ShopUser
     *
     * @throws UserNotFoundException
     */
    protected function getUserByToken($token)
    {
        $criteria = ['authToken' => $token];

        $user = $this
            ->repository
            ->findOneBy($criteria);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        return $user;
    }

    /**
     * @param string $userName
     *
     * @return bool
     */
    protected function userExist($userName)
    {
        $criteria = ['username' => $userName];

        $user = $this
            ->repository
            ->findOneBy($criteria);

        return (bool)$user;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user.rpc';
    }
}
