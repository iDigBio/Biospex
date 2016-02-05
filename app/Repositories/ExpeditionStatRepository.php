<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\ExpeditionStat;
use Biospex\Models\Expedition as Model;

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

