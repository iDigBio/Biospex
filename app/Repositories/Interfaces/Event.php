<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Event extends RepositoryInterface
{
    /**
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes);
}