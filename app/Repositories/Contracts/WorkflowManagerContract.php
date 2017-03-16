<?php

namespace App\Repositories\Contracts;

interface WorkflowManagerContract extends RepositoryContract, CacheableContract
{

    /**
     * Find workflow manager using where and with relations.
     *
     * @param array $where
     * @param array $withRelations
     * @param array $attributes
     * @return mixed
     */
    public function findWhereWithRelations(array $where = [], array $withRelations = [], array $attributes = ['*']);

    /**
     * Find all with relations.
     *
     * @param array $withRelations
     * @param array $attributes
     * @return mixed
     */
    public function findAllWithRelations(array $withRelations = [], array $attributes = ['*']);
}