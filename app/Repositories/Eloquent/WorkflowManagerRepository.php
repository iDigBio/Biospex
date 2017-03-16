<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowManager;
use App\Repositories\Contracts\WorkflowManagerContract;
use Illuminate\Contracts\Container\Container;

class WorkflowManagerRepository extends EloquentRepository implements WorkflowManagerContract
{
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(WorkflowManager::class)
            ->setRepositoryId('biospex.repository.workflowManager');
    }

    /**
     * @inheritdoc
     */
    public function findWhereWithRelations(array $where = [], array $withRelations = [], array $attributes = ['*'])
    {
        return $this->with($withRelations)->findWhere($where, $attributes);
    }

    /**
     * @inheritdoc
     */
    public function findAllWithRelations(array $withRelations = [], array $attributes = ['*'])
    {
        return $this->with($withRelations)->findAll($attributes);
    }
}