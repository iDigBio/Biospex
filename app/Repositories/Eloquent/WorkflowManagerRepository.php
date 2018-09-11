<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowManager as Model;
use App\Repositories\Interfaces\WorkflowManager;

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
        $model =$this->model->with(['expedition.stat', 'expedition.actors' => function($query){
                $query->where('error', 0);
                $query->where('queued', 0);
                $query->where('completed', 0);
            }])->where('stopped', '=', 0);

        $results = $expeditionId === null ?
            $model->get($attributes) :
            $model->where('expedition_id', $expeditionId)->get($attributes);

        $this->resetModel();

        return $results;
    }
}