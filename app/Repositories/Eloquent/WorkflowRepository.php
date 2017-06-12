<?php  

namespace App\Repositories\Eloquent;

use App\Models\Workflow;
use App\Repositories\Contracts\WorkflowContract;
use Illuminate\Contracts\Container\Container;

class WorkflowRepository extends EloquentRepository implements WorkflowContract
{

    /**
     * WorkflowRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Workflow::class)
            ->setRepositoryId('biospex.repository.workflow');
    }
}

