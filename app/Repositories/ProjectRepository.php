<?php namespace App\Repositories;

use App\Repositories\Contracts\Project;
use App\Models\Project as Model;

class ProjectRepository extends Repository implements Project
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Find by url slug
     *
     * @param $slug
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function bySlug($slug)
    {
        return $this->model->bySlug($slug);
    }

    /**
     * Find by uuid
     *
     * @param $uuid
     * @return mixed
     */
    public function findByUuid($uuid)
    {
        return $this->model->findByUuid($uuid);
    }

    /**
     * Override create for relationships and building the advertise column.
     * @param array $data
     * @return mixed
     */
    public function create($data = [])
    {
        $project = $this->model->create($data);
        $project->advertise = $data;
        $project->save();

        return $project;
    }

    public function getSubjectsAssignedCount($project)
    {
        return $this->model->getSubjectsAssignedCount($project);
    }
}
