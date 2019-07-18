<?php

namespace App\Repositories\Eloquent;

use App\Models\Project as Model;
use App\Models\ProjectResource;
use App\Repositories\Interfaces\Project;
use function foo\func;
use Illuminate\Support\Carbon;

class ProjectRepository extends EloquentRepository implements Project
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
    public function getPublicProjectIndex($sort = null, $order = null)
    {
        $results = $this->model->withCount('expeditions')->withCount('events')->with('group')->whereHas('nfnWorkflows')->get();

        $this->resetModel();

        if ($order === null) {
            return $results->sortBy('created_at');
        }

        switch ($sort) {
            case 'title':
                return $order === 'desc' ? $results->sortByDesc('title') : $results->sortBy('title');
            case 'group':
                return $order === 'desc' ? $results->sortByDesc(function ($project) {
                    return $project->group->title;
                }) : $results->sortBy(function ($project) {
                    return $project->group->title;
                });
            case 'date':
                return $order === 'desc' ? $results->sortByDesc('created_at') : $results->sortBy('created_at');
        }
    }

    /**
     * @inheritdoc
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

        $this->resetModel();

        if ($order === null) {
            return $results->sortBy('created_at');
        }

        switch ($sort) {
            case 'title':
                return $order === 'desc' ? $results->sortByDesc('title') : $results->sortBy('title');
            case 'group':
                return $order === 'desc' ? $results->sortByDesc(function ($project) {
                    return $project->group->title;
                }) : $results->sortBy(function ($project) {
                    return $project->group->title;
                });
            case 'date':
                return $order === 'desc' ? $results->sortByDesc('created_at') : $results->sortBy('created_at');
        }
    }

    /**
     * @inheritdoc
     */
    public function getProjectPageBySlug($slug)
    {
        $results = $this->model->withCount('events')->with([
            'group.users.profile',
            'expeditions.stat',
            'expeditions.actors',
            'amChart',
            'resources',
        ])->with([
            'events' => function ($q) {
                $q->orderBy('start_date', 'desc');
            },
        ])->where('slug', '=', $slug)->first();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getProjectShow($projectId)
    {
        $results = $this->model->withCount('expeditions')->with([
                'group',
                'ocrQueue',
                'expeditions' => function($q) {
                    $q->with(['stat', 'nfnActor']);
                },
            ])->find($projectId);

        $this->resetModel();

        return $results;
    }

    /**
     * @param array $attributes
     * @return \App\Repositories\Eloquent\EloquentRepository|bool|\Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes)
    {
        $project = $this->model->create($attributes);

        if (! isset($attributes['resources'])) {
            return true;
        }

        $resources = collect($attributes['resources'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->map(function ($resource) {
            return new ProjectResource($resource);
        });

        $project->resources()->saveMany($resources->all());
    }

    /**
     * Override project update.
     * TODO move resource code
     *
     * @param array $attributes
     * @param $resourceId
     * @return bool
     */
    public function update(array $attributes, $resourceId)
    {
        $model = $this->model->find($resourceId);

        $attributes['slug'] = null;
        $model->fill($attributes)->save();

        if (! isset($attributes['resources'])) {
            return true;
        }

        $resources = collect($attributes['resources'])->reject(function ($resource) {
            return $this->filterOrDeleteResources($resource);
        })->reject(function ($resource) {
            return empty($resource['id']) ? false : $this->updateProjectResource($resource);
        })->map(function ($resource) {
            return new ProjectResource($resource);
        });

        if ($resources->isEmpty()) {
            return true;
        }

        return $model->resources()->saveMany($resources->all());
    }

    /**
     * @inheritdoc
     */
    public function getProjectsHavingTranscriptionLocations(array $projectIds = [])
    {
        $results = empty($projectIds) ? $this->model->has('transcriptionLocations')->get() : $this->model->has('transcriptionLocations')->whereIn('id', $projectIds)->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getProjectEventSelect()
    {
        $results = $this->model->whereHas('nfnWorkflows')->orderBy('title')->get(['id', 'title'])->pluck('title', 'id');

        $this->resetModel();

        return ['' => 'Select'] + $results->toArray();
    }

    /**
     * @param $projectId
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Model[]|mixed|null
     * @throws \Exception
     */
    public function getProjectForDelete($projectId)
    {
        $result = $this->model->with([
            'group',
            'nfnWorkflows',
            'workflowManagers',
            'expeditions.downloads',
        ])->find($projectId);

        $this->resetModel();

        return $result;
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
     * @inheritdoc
     */
    public function getProjectForAmChartJob($projectId)
    {
        $result = $this->model->with([
            'expeditions' => function ($q) {
                $q->with('stat')->has('stat');
                $q->with('nfnWorkflow')->has('nfnWorkflow');
            },
        ])->find($projectId);

        $this->resetModel();

        return $result;
    }
}
