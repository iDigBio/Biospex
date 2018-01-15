<?php namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Workflow extends RepositoryInterface
{
    /**
     * Build select drop down.
     *
     * @return array
     */
    public function getWorkflowSelect();
}


