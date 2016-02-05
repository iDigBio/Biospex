<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\WorkflowManager;
use Biospex\Models\WorkflowManager as Model;

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
     * Find with eager loading
     *
     * @param array $with
     * @return mixed
     */
    public function allWith($with = [])
    {
        return $this->model->allWith($with);
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

    public function findByExpeditionIdWith($id, $with = []) {
        return $this->model->findByExpeditionIdWith($id, $with);
    }
}
