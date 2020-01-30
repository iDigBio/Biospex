<?php

namespace App\Repositories\Eloquent;

use App\Models\ExportQueue as Model;
use App\Repositories\Interfaces\ExportQueue;

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
        $results =  $this->model->where('error', 0)->first($attributes);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function findByIdExpeditionActor($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        $results = $this->model->with(['expedition.actor', 'expedition.project.group.owner'])
            ->whereHas('actor', function ($query) use ($expeditionId, $actorId)
            {
                $query->where('expedition_id', $expeditionId);
                $query->where('actor_id', $actorId);
            })->find($queueId);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function findQueueProcessData($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        $results = $this->model->with(['expedition.actor', 'expedition.project.group'])
            ->whereHas('actor', function ($query) use ($expeditionId, $actorId)
            {
                $query->where('expedition_id', $expeditionId);
                $query->where('actor_id', $actorId);
            })->find($queueId);

        $this->resetModel();

        return $results;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getAllExportQueueOrderByIdAsc()
    {
        $results = $this->model->where('error', 0)->orderBy('id', 'asc')->get('*');

        $this->resetModel();

        return $results;
    }
}