<?php namespace App\Repositories\Contracts;

interface UserGridFieldInterface extends Repository
{
    public function findByUserProjectExpedition($userId, $projectId, $expeditionId);
}
