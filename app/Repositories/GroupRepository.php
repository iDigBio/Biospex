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
}
