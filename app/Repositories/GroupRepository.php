<?php

namespace App\Repositories;

use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class GroupRepository extends Repository implements Group, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Group::class;
    }

    /**
     * Return a specific group by a given name
     * 
     * @param  string $name
     * @return Group
     */
    public function findByName($name)
    {
        return $this->model->findByName($name);
    }
}
