<?php

namespace App\Repositories\Contracts;


interface ActorContract extends RepositoryContract, CacheableContract
{
    /**
     * Get only trashed records
     * @return mixed
     */
    public function getAllTrashed();

    /**
     * @param array $attributes
     * @return mixed
     */
    public function createActor(array $attributes = []);

}