<?php namespace App\Interfaces;

interface Workflow extends Eloquent
{
    /**
     * Build select drop down.
     *
     * @return array
     */
    public function getWorkflowSelect();
}


