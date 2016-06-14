<?php

namespace App\Repositories;

use App\Repositories\Contracts\Team;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class TeamRepository extends Repository implements Team, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\Team::class;
    }
}
