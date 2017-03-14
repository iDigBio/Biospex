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
    public function projectFindWith($projectId, array $relations = [], array $attributes = ['*'])
    {
        return $this->with($relations)->find($projectId, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function getRandomProjectsForCarousel($count = 5)
    {
        return $this->model->inRandomOrder()->whereNotNull('banner_file_name')->limit($count)->get();
    }
}
