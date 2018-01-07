<?php

namespace App\Interfaces;

interface Actor extends Eloquent
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
     * Update actor.
     *
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function updateActor($id, array $attributes = []);

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