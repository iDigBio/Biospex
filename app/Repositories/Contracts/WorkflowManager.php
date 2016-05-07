<?php namespace App\Repositories\Contracts;

interface WorkflowManager extends Repository
{
    /**
     * Get workflow process by expedition id
     *
     * @param $id
     * @return mixed
     */
    public function findByExpeditionId($id);

    /**
     * Find using expedition id with relationships
     * 
     * @param $id
     * @param array $with
     * @return mixed
     */
    public function findByExpeditionIdWith($id, $with);
}
