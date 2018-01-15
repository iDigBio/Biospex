<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Import extends RepositoryInterface
{

    /**
     * @return mixed
     */
    public function getImportsWithoutError();
}