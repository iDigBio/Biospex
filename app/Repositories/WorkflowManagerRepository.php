<?php 

namespace App\Repositories;

use App\Repositories\Contracts\WorkflowManager;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class WorkflowManagerRepository extends Repository implements WorkflowManager, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\WorkflowManager::class;
    }
}
