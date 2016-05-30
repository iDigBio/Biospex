<?php namespace App\Repositories;

use App\Repositories\Contracts\Project;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class ProjectRepository extends Repository implements Project, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Project::class;
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

}
