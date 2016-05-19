<?php namespace App\Repositories;

use App\Repositories\Contracts\ExpeditionStat;
use App\Models\ExpeditionStat as Model;

class ExpeditionStatRepository extends Repository implements ExpeditionStat
{
    /**
     * ExpeditionStatRepository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Find by expedition id.
     * 
     * @param $expeditionId
     * @return mixed
     */
    public function findByExpeditionId($expeditionId)
    {
        return $this->model->findByExpeditionId($expeditionId);
    }
}

