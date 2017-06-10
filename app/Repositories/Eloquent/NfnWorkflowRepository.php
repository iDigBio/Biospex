<?php

namespace App\Repositories\Eloquent;

use App\Models\NfnWorkflow;
use App\Repositories\Contracts\NfnWorkflowContract;
use Illuminate\Contracts\Container\Container;

class NfnWorkflowRepository extends EloquentRepository implements NfnWorkflowContract
{

    /**
     * NfnWorkflowRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(NfnWorkflow::class)
            ->setRepositoryId('biospex.repository.nfnWorkflow');
    }

}