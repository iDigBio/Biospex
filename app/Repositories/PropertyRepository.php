<?php namespace Biospex\Repositories;

use Biospex\Repositories\Contracts\Property;
use Biospex\Models\Property as Model;

class PropertyRepository extends Repository implements Property
{
    /**
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Find by qualified name
     *
     * @param $name
     * @return mixed
     */
    public function findByQualified($name)
    {
        return $this->model->findByQualified($name);
    }

    /**
     * Find by short name
     *
     * @param $name
     * @return mixed
     */
    public function findByShort($name)
    {
        return $this->model->findByShort($name);
    }
}
