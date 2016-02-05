<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\Meta;
use Biospex\Models\Meta as Model;

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
