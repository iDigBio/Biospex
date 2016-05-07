<?php namespace App\Repositories;

use App\Repositories\Contracts\WorkflowManager;
use App\Models\WorkflowManager as Model;

class WorkflowManagerRepository extends Repository implements WorkflowManager
{
    /**
     * @param WorkflowManager $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get workflow process by expedition id
     *
     * @param $id
     * @return mixed
     */
    public function findByExpeditionId($id)
    {
        return $this->model->findByExpeditionId($id);
    }

    /**
     * Find by expedition id with relationship
     * 
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findByExpeditionIdWith($id, $with) {
        return $this->model->findByExpeditionIdWith($id, $with);
    }
}
