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
}
