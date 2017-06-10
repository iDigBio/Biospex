<?php

namespace App\Repositories\Eloquent;

use App\Models\StateCounty;
use App\Repositories\Contracts\StateCountyContract;
use Illuminate\Contracts\Container\Container;

class StateCountyRepository extends EloquentRepository implements StateCountyContract
{
    /**
     * StateCountyRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(StateCounty::class)
            ->setRepositoryId('biospex.repository.stateCounty');

    }

    /**
     * @inheritdoc
     */
    public function truncateTable()
    {
        return $this->truncate();
    }
}