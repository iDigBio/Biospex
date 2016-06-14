<?php

namespace App\Repositories;

use App\Repositories\Contracts\TeamCategory;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class TeamCategoryRepository extends Repository implements TeamCategory, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\TeamCategory::class;
    }
}
