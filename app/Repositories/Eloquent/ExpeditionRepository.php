<?php

namespace App\Repositories\Eloquent;

use App\Models\Expedition as Model;
use App\Repositories\Interfaces\Expedition;

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
    public function getHomePageProjectExpedition()
    {
        $result = $this->model->with([
            'project' => function ($q) {
                $q->withCount('expeditions');
            },
        ])->with('nfnWorkflow')->whereHas('stat', function ($q) {
            $q->whereBetween('percent_completed', [0.00, 99.99]);
        })->with([
            'stat' => function ($q) {
                $q->whereBetween('percent_completed', [0.00, 99.99]);
            },
        ])->where('project_id', 13)->inRandomOrder()->first();

        $this->resetModel();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionPublicPage($sort = null, $order = null)
    {
        $results = $this->model->with('project')
            ->has('nfnWorkflow')
            ->with('nfnWorkflow')->whereHas('stat', function ($q) {
                $q->whereBetween('percent_completed', [0.00, 99.99]);
            })->with([
                'stat' => function ($q) {
                    $q->whereBetween('percent_completed', [0.00, 99.99]);
                },
            ])->get();

        switch ($order) {
            case 'asc':
                $expeditions = $sort === 'title' ? $results->sortBy('title')
                    : $results->sortBy(function ($expedition) {
                    return $expedition->project->title;
                });
                break;
            case 'desc':
                $expeditions = $sort === 'title' ? $results->sortByDesc('title')
                    : $expeditions = $results->sortByDesc(function ($expedition) {
                    return $expedition->project->title;
                });
                break;
            default:
                $expeditions = $results;
        }

        $this->resetModel();

        return $expeditions;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionCompletedPublicPage($sort = null, $order = null)
    {
        $results = $this->model->with('project')
            ->has('nfnWorkflow')
            ->with('nfnWorkflow')
            ->whereHas('stat', function ($q) {
                $q->where('percent_completed', 100.00);
            })->with([
                'stat' => function ($q) {
                    $q->where('percent_completed', 100.00);
                },
            ])->get();

        switch ($order) {
            case 'asc':
                $expeditions = $sort === 'title' ? $results->sortBy('title')
                    : $results->sortBy(function ($expedition) {
                    return $expedition->project->title;
                });
                break;
            case 'desc':
                $expeditions = $sort === 'title' ? $results->sortByDesc('title')
                    : $expeditions = $results->sortByDesc(function ($expedition) {
                    return $expedition->project->title;
                });
                break;
            default:
                $expeditions = $results;
        }

        $this->resetModel();

        return $expeditions;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsForNfnClassificationProcess(array $expeditionIds = [], array $attributes = ['*'])
    {
        $model = $this->model->with([
            'nfnWorkflow',
            'stat',
            'nfnActor',
        ])->has('nfnWorkflow')->whereHas('nfnActor', function ($query) {
            $query->where('completed', 0);
        });

        $results = empty($expeditionIds) ? $model->get($attributes) : $model->whereIn('id', $expeditionIds)->get($attributes);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionSubjectCounts($expeditionId)
    {
        $results = $this->model->find($expeditionId)->subjects()->count();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function expeditionsByUserId($userId, array $relations = [])
    {
        $results = $this->model->with($relations)->whereHas('project.group.users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function expeditionDownloadsByActor($projectId, $expeditionId)
    {
        $results = $this->model->with([
            'project.group',
            'actors.downloads' => function ($query) use ($expeditionId) {
                $query->where('expedition_id', $expeditionId);
            },
        ])->find($expeditionId);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function findExpeditionsByProjectIdWith($projectId, array $with = [])
    {
        $results = $this->model->with($with)->where('project_id', $projectId)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionStats(array $expeditionIds = [], array $columns = ['*'])
    {
        $results = empty($expeditionIds) ? $this->model->has('stat')->with('project')->get($columns) : $this->model->has('stat')->with('project')->whereIn('id', $expeditionIds)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsHavingNfnWorkflows($expeditionId)
    {
        $withRelations = ['nfnWorkflow', 'nfnActor', 'stat'];

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
