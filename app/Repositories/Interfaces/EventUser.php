<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface EventUser extends RepositoryInterface
{
    /**
     * @param $userName
     * @return mixed
     */
    public function getUserByName($userName);
}