<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface PanoptesProject extends RepositoryInterface
{
    /**
     * Find Panoptes project by project id and workflow id.
     *
     * @param $projectId
     * @param $workflowId
     * @return mixed
     */
    public function findByProjectIdAndWorkflowId($projectId, $workflowId);
}