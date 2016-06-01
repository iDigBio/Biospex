<?php 

namespace App\Repositories;

use App\Repositories\Contracts\OcrQueue;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class OcrQueueRepository extends Repository implements OcrQueue, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\OcrQueue::class;
    }
}
