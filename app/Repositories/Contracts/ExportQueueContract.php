<?php

namespace App\Repositories\Contracts;

interface ExportQueueContract extends RepositoryContract, CacheableContract
{

    /**
     * Create export queue record.
     *
     * @param array $attributes
     * @return mixed
     */
    public function firstOrCreateExportQueue(array $attributes = []);

    /**
     * Get queued record or first.
     *
     * @param array $attributes
     * @return mixed
     */
    public function getFirst(array $attributes = ['*']);

    /**
     * Get first ExportQueue Expedition and Actor with pivot table.
     *
     * @param $queueId
     * @param $expeditionId
     * @param $actorId
     * @param array $attributes
     * @return mixed
     */
    public function findByIdExpeditionActor($queueId, $expeditionId, $actorId, array $attributes = ['*']);

}
