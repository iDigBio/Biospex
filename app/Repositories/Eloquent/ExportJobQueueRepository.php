<?php

namespace App\Repositories\Eloquent;

use App\Models\ExportJobQueue;
use App\Repositories\Contracts\ExportJobQueueContract;
use Illuminate\Contracts\Container\Container;

class ExportJobQueueRepository extends BaseEloquentRepository implements ExportJobQueueContract
{
    /**
     * ActorRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(ExportJobQueue::class)
            ->setRepositoryId('biospex.repository.exportJobQue');
    }
}