<?php namespace App\Repositories;

use App\Repositories\Contracts\Actor;
use App\Models\Actor as Model;

class ActorRepository extends Repository implements Actor
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
}
