<?php

namespace App\Repositories\Eloquent;

use App\Models\Project;
use App\Repositories\Contracts\ProjectContract;
use Illuminate\Contracts\Container\Container;

class ProjectRepository extends EloquentRepository implements ProjectContract
{
    /**
     * ProjectRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Project::class)
            ->setRepositoryId('biospex.repository.project');

    }

    /**
     * @inheritdoc
     */
    public function getRandomProjectsForCarousel($count = 5, array $attributes = ['*'])
    {
        return $this->setCacheLifetime(0)
            ->inRandomOrder()
            ->whereNotNull('banner_file_name')
            ->limit($count)
            ->get($attributes);
    }

    /**
     * @inheritdoc
     */
    public function getRecentProjects($count = 5, array $attributes = ['*'])
    {
        return $this->has('nfnWorkflows')
            ->orderBy('id', 'desc')
            ->findAll($attributes)
            ->take($count);
    }

}
