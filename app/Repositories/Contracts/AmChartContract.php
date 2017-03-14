<?php

namespace App\Repositories\Contracts;


interface AmChartContract extends RepositoryContract, CacheableContract
{

    /**
     * Update or create new record.
     *
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreateRecord(array $attributes, array $values = []);
}