<?php namespace App\Repositories\Contracts;

interface ExpeditionStat extends Repository
{

    public function findByExpeditionId($expeditionId);
}