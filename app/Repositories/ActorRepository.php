<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\Actor;
use Biospex\Models\Actor as Model;

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
