<?php 

namespace App\Repositories;

use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class ActorRepository extends Repository implements Actor, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Actor::class;
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
