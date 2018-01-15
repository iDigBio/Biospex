<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Download extends RepositoryInterface
{

    /**
     * @return mixed
     */
    public function getDownloadsForCleaning();
}
