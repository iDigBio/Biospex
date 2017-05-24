<?php

namespace App\Repositories\Contracts;

interface WorkflowManagerContract extends RepositoryContract, CacheableContract
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