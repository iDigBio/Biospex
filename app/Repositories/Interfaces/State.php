<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface State extends RepositoryInterface
{
    /**
     * @return mixed
     */
    public function truncateTable();
}