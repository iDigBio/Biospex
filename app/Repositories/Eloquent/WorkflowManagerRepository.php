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

    /**
     * @inheritdoc
     */
    public function getWorkflowManagersForProcessing($expeditionId = null, array $attributes = ['*'])
    {
        $relations = ['expedition.actors', 'expedition.stat'];
        $where = ['expedition_id', $expeditionId];

        $this->setCacheLifetime(0)
            ->with($relations)
            ->whereHas('expedition.actors', function($query)
            {
                $query->where('error', 0);
                $query->where('queued', 0);
                $query->where('completed', 0);
            })->where('stopped', '=', 0);

        return $expeditionId === null ? $this->findAll($attributes) : $this->findWhere($where, $attributes);
    }
}