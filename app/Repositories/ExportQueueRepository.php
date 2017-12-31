<?php

namespace App\Repositories;

use App\Models\ExportQueue as Model;
use App\Interfaces\ExportQueue;

class ExportQueueRepository extends EloquentRepository implements ExportQueue
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getFirstExportWithoutError(array $attributes = ['*'])
    {
        return $this->model->where('error', 0)->findFirst($attributes);
    }

    /**
     * @inheritdoc
     */
    public function findByIdExpeditionActor($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        return $this->model->with(['expedition.actor', 'expedition.project.group.owner'])
            ->whereHas('actor', function ($query) use ($expeditionId, $actorId)
            {
                $query->where('expedition_id', $expeditionId);
                $query->where('actor_id', $actorId);
            })->find($queueId);
    }

    /**
     * @inheritdoc
     */
    public function findQueueProcessData($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        return $this->model->with(['expedition.actor', 'expedition.project.group'])
            ->whereHas('actor', function ($query) use ($expeditionId, $actorId)
            {
                $query->where('expedition_id', $expeditionId);
                $query->where('actor_id', $actorId);
            })->find($queueId);
    }

    /**
     * @return mixed
     */
    public function getAllExportQueueOrderByIdAsc()
    {
        return $this->model->orderBy('id', 'asc')->get();
    }
}