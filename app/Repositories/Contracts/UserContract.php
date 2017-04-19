<?php

namespace App\Repositories\Contracts;


interface UserContract extends RepositoryContract, CacheableContract
{
    /**
     * Find with relations.
     *
     * @param integer $id
     * @param array|string $relations
     * @return mixed
     */
    public function findWithRelations($id, $relations);
}