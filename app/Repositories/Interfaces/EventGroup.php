<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface EventGroup extends RepositoryInterface
{
    /**
     * @param $uuid
     * @return mixed
     */
    public function getGroupByUuid($uuid);
}