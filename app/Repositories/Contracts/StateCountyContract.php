<?php

namespace App\Repositories\Contracts;


interface StateCountyContract extends RepositoryContract, CacheableContract
{
    /**
     * @return mixed
     */
    public function truncateTable();
}