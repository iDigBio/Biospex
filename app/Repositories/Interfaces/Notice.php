<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Notice extends RepositoryInterface
{

    /**
     * @return mixed
     */
    public function getEnabledNotices();
}