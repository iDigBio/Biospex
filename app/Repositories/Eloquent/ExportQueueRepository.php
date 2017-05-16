<?php

namespace App\Repositories\Eloquent;

use App\Models\ExportQueue;
use App\Repositories\Contracts\ExportQueueContract;
use Illuminate\Contracts\Container\Container;

class ExportQueueRepository extends BaseEloquentRepository implements ExportQueueContract
{
    /**
     * ExpeditionRepository constructor.
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
    public function createExportQueue(array $attributes = [])
    {
        return $this->firstOrCreate($attributes);
    }

    /**
     * @inheritdoc
     */
    public function getFirst(array $attributes = ['*'])
    {
        return $this->findFirst($attributes);
    }

}