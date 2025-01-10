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

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Models\EventTeam;
use App\Services\Event\EventTeamUserService;
use Redirect;
use Session;
use Throwable;
use View;

class EventTeamUserController extends Controller
{
    public function __construct(protected EventTeamUserService $eventTeamUserService) {}

    /**
     * Group join page for events.
     */
    public function create(EventTeam $team): \Illuminate\Contracts\View\View
    {
        if (! isset($team->uuid)) {
            Session::flash('error', t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
        }

        $active = $this->eventTeamUserService->create($team);

        return View::make('front.event.signup', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(
        EventTeam $team,
        EventJoinRequest $request
    ) {

        try {
            $this->eventTeamUserService->store($team, $request->validated());

            return Redirect::route('front.events.show', [$team->event])
                ->with('success', t('Thank you for your registration.'));

        } catch (Throwable $e) {
            Redirect::route('front.events_team_user.create', [$team])
                ->with('danger', t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
        }
    }
}
