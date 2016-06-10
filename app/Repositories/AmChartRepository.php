<?php namespace App\Repositories;

use App\Repositories\Contracts\AmChart;
use App\Repositories\Contracts\CacheableInterface;
use App\Repositories\Traits\CacheableRepository;

class AmChartRepository extends Repository implements AmChart, CacheableInterface
{
    use CacheableRepository;

    /**
     * @return mixed
     */
    public function model()
    {
        return \App\Models\AmChart::class;
    }
}
