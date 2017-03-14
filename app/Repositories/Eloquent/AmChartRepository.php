<?php

namespace App\Repositories\Eloquent;

use App\Models\AmChart;
use App\Repositories\Contracts\AmChartContract;
use Illuminate\Contracts\Container\Container;

class AmChartRepository extends EloquentRepository implements AmChartContract
{
    public function __construct(Container $container)
    {
        $this->setContainer($container)
            ->setModel(AmChart::class)
            ->setRepositoryId('biospex.repository.amChart');
    }

    /**
     * @inheritdoc
     */
    public function updateOrCreateRecord(array $attributes, array $values = [])
    {
        $this->updateOrCreate($attributes, $values);
    }
}