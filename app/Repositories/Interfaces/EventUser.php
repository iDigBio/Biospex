<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface EventUser extends RepositoryInterface
{
    /**
     * @param $name
     * @return mixed
     */
    public function getEventUserByName($name);
}