<?php

namespace App\Repositories\Contracts;


interface AmChartContract extends RepositoryContract, CacheableContract
{
    /**
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreateChart(array $attributes = [], array $values = []);
}