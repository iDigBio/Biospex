<?php namespace App\Repositories\Contracts;

interface UserGridField extends Repository
{
    public function findByUserProjectExpedition($userId, $projectId, $expeditionId);
}
