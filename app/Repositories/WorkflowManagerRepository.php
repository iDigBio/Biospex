<?php 

namespace App\Repositories;

use App\Repositories\Contracts\WorkflowManager;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class WorkflowManagerRepository extends Repository implements WorkflowManager, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\WorkflowManager::class;
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
