<?php 

namespace App\Repositories\Eloquent;

use App\Models\OcrQueue;
use App\Repositories\Contracts\OcrQueueContract;
use Illuminate\Contracts\Container\Container;

class OcrQueueRepository extends EloquentRepository implements OcrQueueContract
{

    /**
     * OcrQueueContractRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(OcrQueue::class)
            ->setRepositoryId('biospex.repository.ocrQueue');
    }
}
