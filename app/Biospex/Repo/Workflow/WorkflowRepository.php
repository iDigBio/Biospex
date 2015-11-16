<?php namespace Biospex\Repo\Workflow;

use Biospex\Repo\Repository;
use Workflow;

class WorkflowRepository extends Repository implements WorkflowInterface
{

    /**
     * WorkflowRepository constructor.
     * @param Workflow $model
     */
    public function __construct(Workflow $model)
    {
        $this->model = $model;
    }
}
