<?php

namespace App\Repositories\Eloquent;

use App\Models\WorkflowManager;
use App\Repositories\Contracts\WorkflowManagerContract;
use App\Repositories\Traits\EloquentRepositoryCommon;
use Illuminate\Contracts\Container\Container;

class WorkflowManagerRepository extends EloquentRepository implements WorkflowManagerContract
{
    use EloquentRepositoryCommon;

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