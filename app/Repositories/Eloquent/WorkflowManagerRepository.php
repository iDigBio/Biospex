<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowManager;
use App\Repositories\Contracts\WorkflowManagerContract;
use Illuminate\Contracts\Container\Container;

class WorkflowManagerRepository extends BaseEloquentRepository implements WorkflowManagerContract
{
    /**
     * WorkflowManagerRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(WorkflowManager::class)
            ->setRepositoryId('biospex.repository.workflowManager');
    }
}