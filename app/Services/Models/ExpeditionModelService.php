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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Models;

use App\Models\Expedition;
use Illuminate\Database\Eloquent\Builder;

readonly class ExpeditionModelService
{
    public function __construct(private Expedition $model) {}

    /**
     * Create expedition.
     */
    public function create(array $data): Expedition
    {
        return $this->model->create($data);
    }

    /**
     * Find expedition with relations.
     */
    public function findExpeditionWithRelations(int $id, array $relations = []): ?Expedition
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Get expeditions for Zooniverse processing.
     */
    public function getExpeditionsForZooniverseProcess(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->with([
            'panoptesProject',
            'stat',
            'zooniverseActor',
        ])->has('panoptesProject')->whereHas('zooniverseActor', function ($query) {
            $query->where('completed', 0);
        })->get();
    }

    /**
     * Get expedition download by actor.
     */
    public function expeditionDownloadsByActor($expeditionId): \Illuminate\Database\Eloquent\Model
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
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getExpeditionAdminIndex($userId = null, $sort = null, $order = null, $projectId = null): \Illuminate\Database\Eloquent\Collection|array
    {
        $query = $this->model->with([
            'project.group',
            'stat',
            'panoptesProject',
            'workflowManager',
            'zooniverseExport',
        ])->whereHas('project.group.users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        });

        return $this->sortResults($projectId, $query, $order, $sort);
    }

    /**
     * Get expedition for home page visuals.
     */
    public function getHomePageProjectExpedition(): \Illuminate\Database\Eloquent\Model
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
     */
    public function findExpeditionHavingWorkflowManager($expeditionId): ?Expedition
    {
        return $this->model->has('workflowManager')->find($expeditionId);
    }

    /**
     * Get expeditions for public index.
     *
     * @param  null  $sort
     * @param  null  $order
     * @param  null  $projectId
     */
    public function getExpeditionPublicIndex($sort = null, $order = null, $projectId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = $this->model->with('project')->has('panoptesProject')->has('zooniverseActor')->with('panoptesProject', 'stat', 'zooniverseActor');

        return $this->sortResults($projectId, $query, $order, $sort);
    }

    /**
     * Find expedition for expert review.
     *
     * @see ExpertReviewSetProblemsJob
     */
    public function findExpeditionForExpertReview(int $expeditionId): \Illuminate\Database\Eloquent\Model
    {
        return $this->model->with([
            'project' => function ($query) {
                $query->select('id', 'group_id')->with([
                    'group' => function ($query) {
                        $query->select('id', 'user_id')->with('owner');
                    },
                ]);
            },
            'zooniverseActor',
        ])->has('panoptesProject')->find($expeditionId);
    }

    /**
     * Get expedition for Zooniverse process.
     *
     * @see ZooniverseCsvService::getExpedition()
     */
    public function getExpeditionForZooniverseProcess(int $expeditionId): \Illuminate\Database\Eloquent\Model
    {
        return $this->model->with(['panoptesProject', 'stat', 'zooniverseActor'])
            ->has('panoptesProject')->whereHas('zooniverseActor', function ($query) {
                $query->where('completed', 0);
            })->find($expeditionId);
    }

    /**
     * Sort results for expedition indexes.
     */
    protected function sortResults($projectId, Builder $query, $order, $sort): \Illuminate\Database\Eloquent\Collection
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
