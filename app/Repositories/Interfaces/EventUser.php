<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface EventUser extends RepositoryInterface
{
    /**
     * @param $name
     * @param array $attributes
     * @return mixed
     */
    public function getUserByName($name, array $attributes = ['*']);
}