<?php

namespace App\Repositories;

use App\Models\Expedition as Model;
use App\Interfaces\Expedition;

class ExpeditionRepository extends EloquentRepository implements Expedition
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
    public function getExpeditionsForNfnClassificationProcess(array $ids = [], array $attributes = ['*'])
    {
        $model = $this->model->with(['nfnWorkflow', 'stat'])->has('nfnWorkflow')
            ->whereHas('actors', function ($query)
            {
                $query->where('completed', 0);
            });

        $results = empty($ids) ?
            $model->get($attributes) :
            $model->whereIn('id', [1, 2, 3])->get($attributes);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionSubjectCounts($id)
    {
        $results = $this->model->find($id)->subjects()->count();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function expeditionsByUserId($userId, array $relations =[])
    {
        $results = $this->model->with($relations)
            ->whereHas('project.group.users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function expeditionDownloadsByActor($expeditionId)
    {
        $results = $this->model->with(['project.group', 'actors.downloads' => function($query) use ($expeditionId){
            $query->where('expedition_id', $expeditionId);
        }])->find($expeditionId);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function findExpeditionsByProjectIdWith($projectId, array $with = [], $trashed = false)
    {
        $results = $trashed ?
            $this->model->onlyTrashed()->with($with)->where('project_id', $projectId)->get() :
            $this->model->with($with)->where('project_id', $projectId)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionStats(array $ids = [], array $columns = ['*'])
    {
        $results =  empty($expeditionIds) ?
            $this->model->has('stat')->with('project')->get($columns) :
            $this->model->has('stat')->with('project')->whereIn('id', $ids)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsHavingNfnWorkflows($expeditionId)
    {
        $withRelations = ['project.amChart', 'nfnWorkflow', 'nfnActor', 'stat'];

        $results = $this->model->has('nfnWorkflow')->with($withRelations)->find($expeditionId);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function findExpeditionHavingWorkflowManager($expeditionId)
    {
        $results = $this->model->has('workflowManager')->find($expeditionId);

        $this->resetModel();

        return $results;
    }
}
