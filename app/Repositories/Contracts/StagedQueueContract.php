<?php

namespace App\Repositories\Contracts;

interface StagedQueueContract extends RepositoryContract, CacheableContract, BaseRepositoryContract
{

    /**
     * Create export queue record.
     *
     * @param array $attributes
     * @return mixed
     */
    public function createStagedQueue(array $attributes = []);

    /**
     * Get queued record or first.
     *
     * @param array $attributes
     * @return mixed
     */
    public function getFirst(array $attributes = ['*']);

    /**
     * Get first StagedQueue Expedition and Actor with pivot table.
     *
     * @param $queueId
     * @param $expeditionId
     * @param $actorId
     * @param array $attributes
     * @return mixed
     */
    public function findByIdWithExpeditionActor($queueId, $expeditionId, $actorId, array $attributes = ['*']);
}
