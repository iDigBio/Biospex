<?php

/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Event;

use App\Models\EventTeam;
use App\Models\EventUser;
use App\Services\Helpers\DateService;

class EventTeamUserService
{
    /**
     * EventTeamUserService constructor.
     */
    public function __construct(
        protected EventUser $eventUser,
        protected EventTeam $eventTeam,
        protected DateService $dateService) {}

    /**
     * Load team relationships for view.
     */
    public function create(EventTeam &$team): bool
    {
        $team->load(['event']);

        return $this->dateService->eventBefore($team->event) || $this->dateService->eventActive($team->event);
    }

    /**
     * Store a newly created user for team signup..
     */
    public function store(EventTeam $eventTeam, array $request): void
    {
        $user = $this->eventUser->updateOrCreate(['nfn_user' => $request['nfn_user']], ['nfn_user' => $request['nfn_user']]);
        $team = $eventTeam->load('event');
        $team->users()->syncWithoutDetaching([$user->id]);
    }
}
