<?php

namespace App\Services\Model;

use App\Repositories\Contracts\WorkflowContract;

class WorkflowService
{

    public $workflowContract;

    public function __construct(WorkflowContract $workflowContract)
    {
        $this->workflowContract = $workflowContract;
    }

    /**
     * Build select drop down.
     *
     * @return array
     */
    public function select()
    {
        return ['--Select--'] + $this->workflowContract->where('enabled', '=',1)
                ->orderBy('title', 'asc')
                ->pluck('title', 'id')
                ->toArray();
    }
}