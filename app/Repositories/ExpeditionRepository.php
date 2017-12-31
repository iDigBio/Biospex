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
        $this->model->with(['nfnWorkflow', 'stat'])->has('nfnWorkflow')
            ->whereHas('actors', function ($query)
            {
                $query->where('completed', 0);
            }, '=');

        return empty($ids) ?
            $this->model->get($attributes) :
            $this->model->whereIn('id', [1, 2, 3])->get($attributes);
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionSubjectCounts($id)
    {
        return $this->model->find($id)->subjects()->count();
    }

    /**
     * @inheritdoc
     */
    public function expeditionsByUserId($userId, array $relations =[])
    {
        return $this->model->with($relations)
            ->whereHas('project.group.users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();
    }

    /**
     * @inheritdoc
     */
    public function expeditionDownloadsByActor($expeditionId)
    {
        return $this->model->with(['project.group', 'actors.downloads' => function($query) use ($expeditionId){
            $query->where('expedition_id', $expeditionId);
        }])->find($expeditionId);
    }

    /**
     * @inheritdoc
     */
    public function findExpeditionsByProjectIdWith($projectId, array $with = [], $trashed = false)
    {
        return $trashed ?
            $this->model->onlyTrashed()->with($with)->where('project_id', $projectId)->get() :
            $this->model->with($with)->where('project_id', $projectId)->get();
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionStats(array $ids = [], array $columns = ['*'])
    {
        return empty($expeditionIds) ?
            $this->model->has('stat')->with('project')->get($columns) :
            $this->model->has('stat')->with('project')->whereIn('id', $ids)->get();
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsHavingNfnWorkflows($expeditionId)
    {
        $withRelations = ['project.amChart', 'nfnWorkflow', 'nfnActor', 'stat'];

        return $this->model->has('nfnWorkflow')->with($withRelations)->find($expeditionId);
    }

    /**
     * @inheritdoc
     */
    public function findExpeditionHavingWorkflowManager($expeditionId)
    {
        return $this->model->has('workflowManager')->find($expeditionId);
    }
}
