<?php  namespace App\Repositories;

use App\Repositories\Contracts\Workflow;
use App\Models\Workflow as Model;

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

