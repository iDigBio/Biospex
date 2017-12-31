<?php

namespace App\Interfaces;

interface WorkflowManager extends Eloquent
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