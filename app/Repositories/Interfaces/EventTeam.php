<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface EventTeam extends RepositoryInterface
{
    /**
     * @param $uuid
     * @return mixed
     */
    public function getTeamByUuid($uuid);
}