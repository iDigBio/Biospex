<?php

namespace App\Repositories;

use App\Models\WorkflowManager as Model;
use App\Interfaces\WorkflowManager;

class WorkflowManagerRepository extends EloquentRepository implements WorkflowManager
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
    public function getWorkflowManagersForProcessing($expeditionId = null, array $attributes = ['*'])
    {
        $this->model->with(['expedition.actors', 'expedition.stat'])
            ->whereHas('expedition.actors', function($query)
            {
                $query->where('error', 0);
                $query->where('queued', 0);
                $query->where('completed', 0);
            })->where('stopped', '=', 0);

        $results = $expeditionId === null ?
            $this->model->get($attributes) : 
            $this->model->where('expedition_id', $expeditionId)->get($attributes);

        $this->resetModel();

        return $results;
    }
}