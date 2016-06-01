<?php  

namespace App\Repositories;

use App\Repositories\Contracts\Workflow;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class WorkflowRepository extends Repository implements Workflow, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Workflow::class;
    }
}

