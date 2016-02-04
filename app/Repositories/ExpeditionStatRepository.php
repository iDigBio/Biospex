<?php namespace App\Repositories;

use App\Repositories\Contracts\ExpeditionStat;
use App\Models\Expedition as Model;

class ExpeditionStatRepository extends Repository implements ExpeditionStat
{
    /**
     * @var \Expedition
     */
    protected $model;

    /**
     * ExpeditionStatRepository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}

