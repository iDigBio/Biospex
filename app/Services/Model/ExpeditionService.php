<?php

namespace App\Services\Model;

use App\Repositories\Contracts\Expedition;

class ExpeditionService
{

    /**
     * @var Expedition
     */
    public $repository;

    /**
     * ExpeditionService constructor.
     *
     * @param Expedition $repository
     */
    public function __construct(Expedition $repository)
    {
        $this->repository = $repository;
    }
}