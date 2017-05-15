<?php

namespace App\Repositories\Eloquent;

use App\Models\ExportQueue;
use App\Repositories\Contracts\ExportQueueContract;
use Illuminate\Contracts\Container\Container;

class ExportQueueRepository extends BaseEloquentRepository implements ExportQueueContract
{
    /**
     * ActorRepository constructor.
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
    public function checkForQueuedJob()
    {
        return $this->findWhere(['queued', '=', 1])->count();
    }
}