<?php namespace Biospex\Repositories\Contracts;

interface WorkflowManager extends Repository
{
    /**
     * Return all records with relationship
     *
     * @param array $with
     * @return mixed
     */
    public function allWith($with = []);

    /**
     * Get workflow process by expedition id
     *
     * @param $id
     * @return mixed
     */
    public function findByExpeditionId($id);

    public function findByExpeditionIdWith($id, $with = []);
}
