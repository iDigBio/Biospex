<?php

namespace App\Repositories\Contracts;

interface SubjectContract extends RepositoryContract, CacheableContract
{

    /**
     * Find subject by id.
     *
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function findById($id, array $attributes = ['*']);
}