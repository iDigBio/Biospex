<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

interface EventTeam extends RepositoryInterface
{
    /**
     * @param $uuid
     * @return mixed
     */
    public function getTeamByUuid($uuid);

    /**
     * @param string $eventId
     * @return \Illuminate\Support\Collection
     */
    public function getTeamsByEventId(string $eventId): Collection;
}