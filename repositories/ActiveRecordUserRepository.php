<?php

namespace app\repositories;

use app\models\ShopUser;

/**
 * Class ArrayUserRepository
 * @package app\repositories
 */
class ActiveRecordUserRepository implements UserRepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id
     *
     * @return ShopUser The object.
     */
    public function find($id)
    {
        return ShopUser::findOne($id);
    }

    /**
     * Finds all objects in the repository
     *
     * @return ShopUser[] The objects.
     */
    public function findAll()
    {
        return ShopUser::find()->all();
    }

    /**
     * Finds objects by a set of criteria
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return ShopUser[] The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $query = ShopUser::find()
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
     * @return ShopUser The object.
     */
    public function findOneBy(array $criteria)
    {
        return ShopUser::find()
            ->where($criteria)
            ->one();
    }
}
