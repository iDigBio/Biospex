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
     * Check if a NfN workflow empty.
     *
     * @param array $nfnWorkflows
     * @return bool
     */
    public function checkNfnWorkflowsEmpty($nfnWorkflows)
    {
        return null === $nfnWorkflows || $nfnWorkflows->isEmpty();
    }
}