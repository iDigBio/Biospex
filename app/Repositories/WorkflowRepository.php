<?php  namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\Workflow;
use Biospex\Models\Workflow as Model;

class WorkflowRepository extends Repository implements Workflow
{
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function selectList($value, $id)
    {
        return $this->model->selectList($value, $id);
    }
}

