<?php

namespace App\Repositories\Eloquent;

use App\Models\Project as Model;
use App\Models\ProjectResource;
use App\Repositories\Interfaces\Project;

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

    public function create(array $attributes)
    {
        $project = $this->model->create($attributes);

        if ( ! isset($attributes['resources'])) {
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

        if ( ! isset($attributes['resources'])) {
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
    public function getProjectByIdWith($projectId, array $with = [])
    {
        $results = $this->model->with($with)->find($projectId);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getRandomProjectsForCarousel($count = 5, array $attributes = ['*'])
    {
        $results = $this->model->inRandomOrder()->whereNotNull('banner_file_name')->limit($count)->get($attributes);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getRecentProjects($count = 5, array $attributes = ['*'])
    {
        $results = $this->model->whereHas('nfnWorkflows')->orderBy('created_at', 'desc')->limit($count)->get($attributes);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getProjectPageBySlug($slug)
    {
        $results = $this->model->with([
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
        $result = $this->model->with(['expeditions' => function($q) {
            $q->with('stat')->has('stat');
            $q->with('nfnWorkflow')->has('nfnWorkflow');
        }])->find($projectId);

        $this->resetModel();

        return $result;
    }
}
