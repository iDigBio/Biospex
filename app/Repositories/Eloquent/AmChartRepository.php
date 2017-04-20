<?php

namespace App\Repositories\Eloquent;

use App\Models\AmChart;
use App\Repositories\Contracts\AmChartContract;
use App\Repositories\Traits\EloquentRepositoryCommon;
use Illuminate\Contracts\Container\Container;

class AmChartRepository extends EloquentRepository implements AmChartContract
{
    use EloquentRepositoryCommon;

    /**
     * AmChartRepository constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(AmChart::class)
            ->setRepositoryId('biospex.repository.amChart');
    }
}