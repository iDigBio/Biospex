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
    public function findAllHasRelationsWithRelations(array $hasRelations = [], array $relations = [], array $attributes = ['*'])
    {
        foreach ($hasRelations as $relation)
        {
            $this->has($relation);
        }

        return $this->with($relations)->findAll($attributes);
    }

    /**
     * @inheritdoc
     */
    public function findWithRelations($projectId, array $relations = [], array $attributes = ['*'])
    {
        return $this->with($relations)->find($projectId, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function findWhereInHasRelationsWithRelations($attributeValues, array $hasRelations = [], array $relations = [], array $attributes = ['*'])
    {
        foreach ($hasRelations as $relation)
        {
            $this->has($relation);
        }

        return $this->with($relations)->findWhereIn($attributeValues, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function getRandomProjectsForCarousel($count = 5)
    {
        return $this->model->inRandomOrder()->whereNotNull('banner_file_name')->limit($count)->get();
    }
}
