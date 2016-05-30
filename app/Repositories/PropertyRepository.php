<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Property;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class PropertyRepository extends Repository implements Property, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Property::class;
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
