<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface WorkflowManager extends RepositoryInterface
{

    /**
     * Get workflow managers for processing.
     *
     * @param array $expeditionId
     * @param array $attributes
     * @return mixed
     */
    public function getWorkflowManagersForProcessing($expeditionId = null, array $attributes = ['*']);
}