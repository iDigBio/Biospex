<?php

namespace App\Repositories\Eloquent;

use App\Models\StagedQueue;
use App\Repositories\Contracts\StagedQueueContract;
use Illuminate\Contracts\Container\Container;

class StagedQueueRepository extends BaseEloquentRepository implements StagedQueueContract
{
    /**
     * ExpeditionRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(StagedQueue::class)
            ->setRepositoryId('biospex.repository.stagedQueue');
    }

    /**
     * @inheritdoc
     */
    public function createStagedQueue(array $attributes = [])
    {
        return $this->firstOrCreate($attributes);
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
    public function findByIdWithExpeditionActor($queueId, $expeditionId, $actorId, array $attributes = ['*'])
    {
        return $this->with(['expedition.actor'])->whereHas('actor', function ($query) use ($expeditionId, $actorId)
            {
                $query->where('expedition_id', $expeditionId);
                $query->where('actor_id', $actorId);
            }, '=')->find($queueId);
    }

}