<?php

namespace App\Repositories\Eloquent;

use App\Models\AmChart;
use App\Repositories\Contracts\AmChartContract;
use Illuminate\Contracts\Container\Container;

class AmChartRepository extends EloquentRepository implements AmChartContract
{
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

    /**
     * @inheritdoc
     */
    public function updateOrCreateChart(array $attributes = [], array $values = [])
    {
        $entity = $this->updateOrCreate($attributes, $values);
        $this->flushCacheKeys();

        return $entity;
    }
}