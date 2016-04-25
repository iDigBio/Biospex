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

    /**
     * Find record using title
     * @param $value
     * @return mixed
     */
    public function findByTitle($value)
    {
        return $this->model->findByTitle($value);
    }
}
