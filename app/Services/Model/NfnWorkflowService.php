<?php

namespace App\Services\Model;


use App\Repositories\Contracts\NfnWorkflow;

class NfnWorkflowService
{

    public $repository;

    /**
     * NfnWorkflowService constructor.
     * @param NfnWorkflow $repository
     */
    public function __construct(NfnWorkflow $repository)
    {
        $this->nfnWorkflow = $repository;
    }

    /**
     * Check if a NfN workflows empty.
     *
     * @param $record
     * @return bool
     */
    public function checkNfnWorkflowsEmpty($record)
    {
        return null === $record->nfnWorkflows || $record->nfnWorkflows->isEmpty();
    }

    /**
     * Check workflow is empty.
     *
     * @param $record
     * @return bool
     */
    public function checkNfnWorkflowEmpty($record)
    {
        return empty($record->nfnWorkflow) || null === $record->nfnWorkflow;
    }
}