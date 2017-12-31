<?php

namespace App\Repositories;

use App\Models\Project as Model;
use App\Interfaces\Project;
use Spiritix\LadaCache\Database\LadaCacheTrait;

class ProjectRepository extends EloquentRepository implements Project
{
    use LadaCacheTrait;

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
    public function getProjectByIdWith($projectId, array $with = [], $trashed = false)
    {
        return $trashed ?
            $this->model->onlyTrashed()->with($with)->find($projectId) :
            $this->model->with($with)->find($projectId);
    }

    /**
     * @inheritdoc
     */
    public function getRandomProjectsForCarousel($count = 5, array $attributes = ['*'])
    {
        $results = $this->model->inRandomOrder()
            ->whereNotNull('banner_file_name')
            ->limit($count)
            ->get($attributes);

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getRecentProjects($count = 5, array $attributes = ['*'])
    {
        $results = $this->model
            ->whereHas('nfnWorkflows')
            ->orderBy('created_at', 'desc')
            ->limit($count)
            ->get($attributes);

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getProjectPageBySlug($slug)
    {
        $results = $this->model
            ->with(['group.users.profile', 'expeditions.stat', 'expeditions.actors', 'amChart'])
            ->where('slug', '=', $slug)
            ->first();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getProjectsHavingTranscriptionLocations(array $ids = [])
    {
        return empty($ids) ?
            $this->model->has('transcriptionLocations')->get() :
            $this->model->has('transcriptionLocations')->whereIn('id', $ids)->get();
    }
}
