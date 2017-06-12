<?php

namespace App\Repositories\Eloquent;

use App\Models\Team;
use App\Repositories\Contracts\TeamContract;
use Illuminate\Contracts\Container\Container;

class TeamRepository extends EloquentRepository implements TeamContract
{

    /**
     * TeamContractRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(Team::class)
            ->setRepositoryId('biospex.repository.team');
    }
}
