<?php

namespace app\repositories;

use app\models\Product;

/**
 * Class ActiveRecordProductRepository
 * @package app\repositories
 */
class ActiveRecordProductRepository implements ProductRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id
     *
     * @return Product
     */
    public function find($id)
    {
        return Product::findOne($id);
    }

    /**
     * Finds all objects in the repository
     *
     * @return Product[]
     */
    public function findAll()
    {
        return Product::find()->all();
    }

    /**
     * Finds objects by a set of criteria
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return Product[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $query = Product::find()
            ->where($criteria);

        if ($orderBy) {
            $query->addOrderBy($orderBy);
        }

        if ($limit) {
            $query->limit($limit);
        }

        if ($offset) {
            $query->offset($offset);
        }

        return $query->all();
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     *
     * @return Product
     */
    public function findOneBy(array $criteria)
    {
        return Product::find()
            ->where($criteria)
            ->one();
    }
}
