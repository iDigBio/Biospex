<?php namespace App\Repositories;

use App\Repositories\Contracts\Meta;
use App\Models\Meta as Model;

class MetaRepository extends Repository implements Meta
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
