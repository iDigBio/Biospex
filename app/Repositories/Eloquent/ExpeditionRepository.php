<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Repositories\Eloquent;

use App\Models\Expedition as Model;
use App\Repositories\Interfaces\Expedition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
        return $this->model->with([
            'project' => function ($q) {
                $q->withCount('expeditions');
                $q->withCount('events');
            },
        ])->with('panoptesProject')->whereHas('stat', function ($q) {
            $q->whereBetween('percent_completed', [0.00, 99.99]);
        })->with([
            'stat' => function ($q) {
                $q->whereBetween('percent_completed', [0.00, 99.99]);
            },
        ])->where('project_id', 13)->inRandomOrder()->first();
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionPublicIndex($sort = null, $order = null, $projectId = null)
    {
        $query = $this->model->with('project')->has('panoptesProject')->has('nfnActor')->with('panoptesProject', 'stat', 'nfnActor');

        return $this->sortResults($projectId, $query, $order, $sort);
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionAdminIndex($userId = null, $sort = null, $order = null, $projectId = null)
    {
        $query = $this->model->with([
            'project.group',
            'stat',
            'nfnActor',
            'panoptesProject',
        ])->whereHas('project.group.users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        return $this->sortResults($projectId, $query, $order, $sort);
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsForNfnClassificationProcess(array $expeditionIds = [], array $attributes = ['*'])
    {
        $model = $this->model->with([
            'panoptesProject',
            'stat',
            'nfnActor',
        ])->has('panoptesProject')->whereHas('nfnActor', function ($query) {
            $query->where('completed', 0);
        });

        return empty($expeditionIds) ? $model->get($attributes) : $model->whereIn('id', $expeditionIds)->get($attributes);
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionSubjectCounts($expeditionId)
    {
        return $this->model->find($expeditionId)->subjects()->count();
    }

    /**
     * @inheritdoc
     */
    public function expeditionsByUserId($userId, array $relations = [])
    {
        $relations = ['stat', 'downloads', 'actors', 'project.group'];

        return $this->model->with($relations)->whereHas('project.group.users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    /**
     * @inheritdoc
     */
    public function expeditionDownloadsByActor($projectId, $expeditionId)
    {
        return $this->model->with([
            'project.group',
            'actors.downloads' => function ($query) use ($expeditionId) {
                $query->where('expedition_id', $expeditionId);
            },
        ])->find($expeditionId);
    }

    /**
     * @inheritdoc
     */
    public function findExpeditionsByProjectIdWith($projectId, array $with = [])
    {
        return $this->model->with($with)->where('project_id', $projectId)->get();
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionStats(array $expeditionIds = [], array $columns = ['*'])
    {
        return empty($expeditionIds) ? $this->model->has('stat')->with('project')->get($columns) : $this->model->has('stat')->with('project')->whereIn('id', $expeditionIds)->get();
    }

    /**
     * @inheritdoc
     */
    public function getExpeditionsHavingPanoptesProjects($expeditionId)
    {
        $withRelations = ['panoptesProject', 'nfnActor', 'stat'];

        return $this->model->has('panoptesProject')->with($withRelations)->find($expeditionId);
    }

    /**
     * @inheritdoc
     */
    public function findExpeditionHavingWorkflowManager($expeditionId)
    {
        return $this->model->has('workflowManager')->find($expeditionId);
    }

    /**
     * Sort results for expedition indexes.
     *
     * @param $projectId
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $order
     * @param $sort
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    protected function sortResults($projectId, Builder $query, $order, $sort)
    {
        $results = $projectId === null ? $query->get() : $query->where('project_id', $projectId)->get();

        if ($order === null) {
            return $results;
        }

        switch ($sort) {
            case 'title':
                $results = $order === 'desc' ? $results->sortByDesc('title') : $results->sortBy('title');
                break;
            case 'project':
                $results = $order === 'desc' ? $results->sortByDesc(function ($expedition) {
                    return $expedition->project->title;
                }) : $results->sortBy(function ($expedition) {
                    return $expedition->project->title;
                });
                break;
            case 'date':
                $results = $order === 'desc' ? $results->sortByDesc('created_at') : $results->sortBy('created_at');
                break;
        }

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function findExpeditionForExpertReview(int $expeditionId)
    {
        return $this->model->with([
            'project' => function ($query) {
                $query->select('id', 'group_id')->with([
                    'group' => function ($query) {
                        $query->select('id', 'user_id')->with('owner');
                    },
                ]);
            },
            'nfnActor',
        ])->has('panoptesProject')->find($expeditionId);
    }
}
