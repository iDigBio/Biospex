<?php
/*
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

namespace App\Repositories;

use App\Models\Expedition;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class ExpeditionRepository
 *
 * @package App\Repositories
 */
class ExpeditionRepository extends BaseRepository
{
    /**
     * ExpeditionRepository constructor.
     *
     * @param \App\Models\Expedition $expedition
     */
    public function __construct(Expedition $expedition)
    {

        $this->model = $expedition;
    }

    /**
     * Get expeditions for NfnPanoptes processing.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getExpeditionsForZooniverseProcess()
    {
        return $this->model->with([
            'panoptesProject',
            'stat',
            'nfnActor',
        ])->has('panoptesProject')->whereHas('nfnActor', function ($query) {
            $query->where('completed', 0);
        })->get();
    }

    /**
     * Get expedition download by actor.
     *
     * @param $expeditionId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|Expedition|null
     */
    public function expeditionDownloadsByActor($expeditionId)
    {
        return $this->model->with([
            'project.group',
            'actors.downloads' => function ($query) use ($expeditionId) {
                $query->where('expedition_id', $expeditionId);
            },
        ])->find($expeditionId);
    }

    /**
     * Get expeditions for admin index.
     *
     * @param null $userId
     * @param null $sort
     * @param null $order
     * @param null $projectId
     * @return mixed
     */
    public function getExpeditionAdminIndex($userId = null, $sort = null, $order = null, $projectId = null)
    {
        $query = $this->model->with([
            'project.group',
            'stat',
            'panoptesProject',
            'workflowManager',
            'export'
        ])->whereHas('project.group.users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        return $this->sortResults($projectId, $query, $order, $sort);
    }

    /**
     * Get expedition for home page visuals.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
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
     * Find expedition having workflow manager by id.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function findExpeditionHavingWorkflowManager($expeditionId)
    {
        return $this->model->has('workflowManager')->find($expeditionId);
    }

    /**
     * Get expeditions for public index.
     *
     * @param null $sort
     * @param null $order
     * @param null $projectId
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getExpeditionPublicIndex($sort = null, $order = null, $projectId = null)
    {
        $query = $this->model->with('project')->has('panoptesProject')->has('nfnActor')->with('panoptesProject', 'stat', 'nfnActor');

        return $this->sortResults($projectId, $query, $order, $sort);
    }

    /**
     * Find expedition for expert review.
     *
     * @param int $expeditionId
     * @return \App\Models\Expedition|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
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

    /**
     * @param int $expeditionId
     * @return \App\Models\Expedition|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function getExpeditionForZooniverseProcess(int $expeditionId)
    {
        return $this->model->with(['panoptesProject', 'stat', 'nfnActor'])
            ->has('panoptesProject')->whereHas('nfnActor', function ($query) {
                $query->where('completed', 0);
            })->find($expeditionId);
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
}