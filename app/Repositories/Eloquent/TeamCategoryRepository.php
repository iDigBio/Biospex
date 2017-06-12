<?php

namespace App\Repositories\Eloquent;

use App\Models\TeamCategory;
use App\Repositories\Contracts\TeamCategoryContract;
use Illuminate\Contracts\Container\Container;

class TeamCategoryRepository extends EloquentRepository implements TeamCategoryContract
{

    /**
     * TeamCategoryRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(TeamCategory::class)
            ->setRepositoryId('biospex.repository.teamCategory');
    }
}
