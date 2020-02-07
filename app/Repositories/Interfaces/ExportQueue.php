<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface ExportQueue extends RepositoryInterface
{
    /**
     * Get queued record or first.
     *
     * @param array $attributes
     * @return mixed
     */
    public function getFirstExportWithoutError(array $attributes = ['*']);

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

    /**
     * @param $queueId
     * @param $expeditionId
     * @param $actorId
     * @param array $attributes
     * @return mixed
     */
    public function findQueueProcessData($queueId, $expeditionId, $actorId, array $attributes = ['*']);

    /**
     * @return mixed
     */
    public function getAllExportQueueOrderByIdAsc();

    /**
     * Get remaining batch count.
     *
     * @param string $expeditionId
     * @return string
     */
    public function getBatchRemainingCount(string $expeditionId): string;

}
