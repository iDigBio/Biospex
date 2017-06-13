<?php

namespace App\Repositories\Eloquent;

use App\Models\ExportQueue;
use App\Repositories\Contracts\ExportQueueContract;
use Illuminate\Contracts\Container\Container;

class ExportQueueRepository extends EloquentRepository implements ExportQueueContract
{

    /**
     * ExportQueueRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(ExportQueue::class)
            ->setRepositoryId('biospex.repository.exportQueue');
    }

    /**
     * @inheritdoc
     */
    public function firstOrCreateExportQueue(array $attributes = [])
    {
        $entity = $this->firstOrCreate($attributes);
        $this->getContainer('events')->fire($this->getRepositoryId().'.entity.created', [$this, $entity]);

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function getFirst(array $attributes = ['*'])
    {
        return $this->where('error', '=', 0)->findFirst($attributes);
    }

    /**
     * @inheritdoc
     */
    public function findByIdExpeditionActor($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        return $this->with(['expedition.actor'])
            ->whereHas('actor', function ($query) use ($expeditionId, $actorId)
            {
                $query->where('expedition_id', $expeditionId);
                $query->where('actor_id', $actorId);
            }, '=')->find($queueId);
    }

    /**
     * @inheritdoc
     */
    public function findQueueProcessData($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        return $this->with(['expedition.actor', 'expedition.project.group'])
            ->whereHas('actor', function ($query) use ($expeditionId, $actorId)
            {
                $query->where('expedition_id', $expeditionId);
                $query->where('actor_id', $actorId);
            }, '=')->find($queueId);
    }
}