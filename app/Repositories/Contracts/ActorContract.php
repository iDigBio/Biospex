<?php

namespace App\Repositories\Contracts;


interface ActorContract extends RepositoryContract, CacheableContract
{

    /**
     * Find with relations.
     * @param $id
     * @param array $relations
     * @return mixed
     */
    public function findWithRelations($id, array $relations = []);

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