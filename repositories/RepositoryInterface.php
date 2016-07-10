<?php

namespace app\repositories;

/**
 * Interface RepositoryInterface
 * @package app\repositories
 */
interface RepositoryInterface
{
    /**
     * Finds an object by its primary key / identifier.
     *
     * @param string $id
     *
     * @return object The object.
     */
    public function find($id);

    /**
     * Finds all objects in the repository
     *
     * @return array The objects.
     */
    public function findAll();

    /**
     * Finds objects by a set of criteria
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     *
     * @return object The object.
     */
    public function findOneBy(array $criteria);
}
