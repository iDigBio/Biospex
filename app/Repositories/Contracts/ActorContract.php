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
     * Create new Actor.
     *
     * @param array $attributes
     * @return mixed
     */
    public function createActor(array $attributes = []);

    /**
     * Update existing pivot table for ActorExpeditions.
     *
     * @param $actor
     * @param $expeditionId
     * @param array $attributes
     * @return mixed
     */
    public function updateActorExpeditionPivot($actor, $expeditionId, array $attributes = []);

}