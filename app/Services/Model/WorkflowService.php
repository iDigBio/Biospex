<?php

namespace App\Services\Model;


use App\Repositories\Contracts\Workflow;

class WorkflowService
{

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Build select drop down.
     *
     * @return array
     */
    public function select()
    {
        return ['--Select--'] + $this->workflow->where(['enabled' => 1])
                ->orderBy(['title' => 'asc'])
                ->pluck('title', 'id')
                ->toArray();
    }
}