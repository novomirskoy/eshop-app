<?php

namespace app\websocket\rpc;

use app\models\Product;
use app\repositories\ProductRepositoryInterface;
use Novomirskoy\Websocket\Router\WampRequest;
use Novomirskoy\Websocket\RPC\RpcInterface;
use Ratchet\ConnectionInterface;

/**
 * Class ProductRpc
 * @package app\websocket\rpc
 */
class ProductRpc implements RpcInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;
    
    /**
     * ProductRpc constructor.
     * 
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param $params
     *
     * @return array
     */
    public function getAll(ConnectionInterface $connection, WampRequest $request, $params)
    {
        $products = $this->repository->findAll();
        $productsData = [];

        foreach ($products as $product) {
            $productsData[] = [
                'id' => $product->id,
                'description' => $product->description,
                'price' => $product->price,
                'quantity' => $product->quantity,
                'image' => $product->image,
            ];
        }

        return ['result' => $productsData];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'product.rpc';
    }
}
