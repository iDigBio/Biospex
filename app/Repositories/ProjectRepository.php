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

use App\Models\Project;
use App\Models\ProjectResource;
use function collect;

/**
 * Class ProjectRepository
 *
 * @package App\Repositories
 */
class ProjectRepository extends BaseRepository
{
    /**
     * ProjectRepository constructor.
     *
     * @param \App\Models\Project $project
     */
    public function __construct(Project $project)
    {

        $this->model = $project;
    }

    /**
     * @param array $data
     * @return \App\Models\Project|\Illuminate\Database\Eloquent\Model|true
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model|bool|Project
    {
        $project = $this->model->create($data);

        if (! isset($data['resources'])) {
            return true;
        }

        $resources = collect($data['resources'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->map(function ($resource) {
            return new ProjectResource($resource);
        });

        $project->resources()->saveMany($resources->all());

        return $project;
    }

    /**
     * Override project update.
     *
     * TODO move resource code
     * @param array $data
     * @param $resourceId
     * @return bool|iterable
     */
    public function update(array $data, $resourceId)
    {
        $model = $this->model->find($resourceId);

        $data['slug'] = null;
        $model->fill($data)->save();

        if (! isset($data['resources'])) {
            return true;
        }

        $resources = collect($data['resources'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->reject(function ($resource) {
            return ! empty($resource['id']) && $this->updateProjectResource($resource);
        })->map(function ($resource) {
            return new ProjectResource($resource);
        });

        if ($resources->isEmpty()) {
            return true;
        }

        return $model->resources()->saveMany($resources->all());
    }

    /**
     * Get select for project.
     *
     * @return array|string[]
     */
    public function getProjectEventSelect()
    {
        $results = $this->model->has('panoptesProjects')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->pluck('title', 'id');

        return ['' => 'Select'] + $results->toArray();
    }

    /**
     * Get projects for admin index page.
     *
     * @param $userId
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getAdminProjectIndex($userId, $sort = null, $order = null)
    {
        $results = $this->model->withCount('expeditions')->with([
            'group' => function ($q) use ($userId) {
                $q->whereHas('users', function ($q) use ($userId) {
                    $q->where('users.id', $userId);
                });
            },
        ])->whereHas('group', function ($q) use ($userId) {
            $q->whereHas('users', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            });
        })->get();

        return $this->sortResults($order, $results, $sort);
    }

    /**
     * Get public project index page.
     *
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getPublicProjectIndex($sort = null, $order = null)
    {
        $results = $this->model->withCount('expeditions')
            ->withCount('events')->with('group')->has('panoptesProjects')->get();

        return $this->sortResults($order, $results, $sort);
    }

    /**
     * @param $projectId
     * @return \App\Models\Project|null
     */
    public function getProjectShow($projectId): ?Project
    {
        return $this->model->withCount('expeditions')->with([
            'group',
            'ocrQueue',
            'expeditions' => function($q) {
                $q->with(['stat', 'zooniverseExport', 'panoptesProject', 'workflowManager']);
            }
        ])->find($projectId);
    }

    /**
     * Get project page by slug.
     *
     * @param $slug
     * @return \App\Models\Project|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getProjectPageBySlug($slug)
    {
        return $this->model->withCount('events')->withCount('expeditions')->with([
            'group.users.profile',
            'resources',
            'lastPanoptesProject',
            'bingos',
            'expeditions' => function($query){
                $query->has('panoptesProject')->has('zooniverseActor')->with('panoptesProject', 'stat', 'zooniverseActor');
            },
            'events' => function ($q) {
                $q->with('teams');
                $q->orderBy('start_date', 'desc');
            }])->where('slug', '=', $slug)->first();
    }

    /**
     * Get project for deletion.
     *
     * @param $projectId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder[]
     */
    public function getProjectForDelete($projectId): \Illuminate\Database\Eloquent\Builder|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {
        return $this->model->with([
            'group',
            'panoptesProjects',
            'workflowManagers',
            'expeditions.downloads',
        ])->find($projectId);
    }

    /**
     * Filter or delete resource.
     *
     * @param $resource
     * @return bool
     */
    public function filterOrDeleteResources($resource)
    {
        if ($resource['type'] === null) {
            return true;
        }

        if ($resource['type'] === 'delete') {
            ProjectResource::destroy($resource['id']);

            return true;
        }

        return false;
    }

    /**
     * Update project resource.
     *
     * @param $resource
     * @return bool
     */
    public function updateProjectResource($resource)
    {
        $record = ProjectResource::find($resource['id']);
        $record->type = $resource['type'];
        $record->name = $resource['name'];
        $record->description = $resource['description'];
        if (isset($resource['download'])) {
            $record->download = $resource['download'];
        }

        $record->save();

        return true;
    }

    /**
     * Sort results from index pages.
     *
     * @param $order
     * @param $results
     * @param $sort
     * @return mixed
     */
    protected function sortResults($order, $results, $sort)
    {
        if ($order === null) {
            return $results->sortBy('created_at');
        }

        switch ($sort) {
            case 'title':
                $results = $order === 'desc' ? $results->sortByDesc('title') : $results->sortBy('title');
                break;
            case 'group':
                $results = $order === 'desc' ? $results->sortByDesc(function ($project) {
                    return $project->group->title;
                }) : $results->sortBy(function ($project) {
                    return $project->group->title;
                });
                break;
            case 'date':
                $results = $order === 'desc' ? $results->sortByDesc('created_at') : $results->sortBy('created_at');
                break;
        }

        return $results;
    }

    /**
     * Get project for amChart.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectForAmChartJob($projectId)
    {
        return $this->model->with([
            'amChart',
            'expeditions' => function ($q) {
                $q->with('stat')->has('stat');
                $q->with('panoptesProject')->has('panoptesProject');
            },
        ])->find($projectId);
    }

    /**
     * @param $projectId
     * @return mixed
     */
    public function getProjectForDarwinImportJob($projectId)
    {
        return $this->model->with(['workflow.actors', 'group' => function($q){
            $q->with(['owner', 'users' => function($q){
                $q->where('notification', 1);
            }]);
        }])->find($projectId);
    }
}