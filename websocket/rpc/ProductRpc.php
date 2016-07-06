<?php

namespace app\websocket\rpc;

use app\models\Product;
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
     * @param ConnectionInterface $connection
     * @param WampRequest $request
     * @param $params
     *
     * @return array
     */
    public function getAll(ConnectionInterface $connection, WampRequest $request, $params)
    {
        /** @var Product[] $products */
        $products = Product::find()->all();
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
