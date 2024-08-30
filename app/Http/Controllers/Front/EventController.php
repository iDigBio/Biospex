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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Models\EventTeam;
use App\Services\Models\EventModelService;
use App\Services\Models\EventUserModelService;
use Date;
use General;

/**
 * Class EventController
 *
 * @package App\Http\Controllers\Front
 */
class EventController extends Controller
{
    public function __construct(private readonly EventModelService $eventModelService)
    {}

    /**
     * Displays Events on public page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $results = $this->eventModelService->getEventPublicIndex();

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return Date::eventBefore($event) || Date::eventActive($event);
        });

        return \View::make('front.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     *
     * @return \Illuminate\Contracts\View\View|null
     */
    public function sort(): ?\Illuminate\Contracts\View\View
    {
        if (! \Request::ajax()) {
            return null;
        }

        $sort = \Request::get('sort');
        $order = \Request::get('order');
        $projectId = \Request::get('id');

        $results = $this->eventModelService->getEventPublicIndex($sort, $order, $projectId);

        [$active, $completed] = $results->partition(function ($event) {
            return Date::eventBefore($event) || Date::eventActive($event);
        });

        $events = \Request::get('type') === 'active' ? $active : $completed;

        return \View::make('front.event.partials.event', compact('events'));
    }

    /**
     * Display the show page for an event.
     *
     * @param int $eventId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function read(int $eventId): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $event = $this->eventModelService->findEventWithRelations($eventId, ['project.lastPanoptesProject', 'teams:id,title,event_id']);

        if ($event === null) {
            \Flash::error(t('Error retrieving record from database'));

            return \Redirect::route('front.events.index');
        }

        return \View::make('front.event.show', compact('event'));
    }

    /**
     * Group join page for events.
     *
     * @param \App\Models\EventTeam $eventTeam
     * @param $uuid
     * @return \Illuminate\Contracts\View\View
     */
    public function signup(EventTeam $eventTeam, $uuid): \Illuminate\Contracts\View\View
    {
        $team = $eventTeam->with(['event'])->where('uuid', General::uuidToBin($uuid))->first();

        $active = Date::eventBefore($team->event) || Date::eventActive($team->event);

        if ($team === null) {
            \Flash::error(t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
        }

        return \View::make('front.event.signup', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
     * @param \App\Services\Models\EventUserModelService $eventUserModelService
     * @param \App\Models\EventTeam $eventTeam
     * @param \App\Http\Requests\EventJoinRequest $request
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(
        EventUserModelService $eventUserModelService,
        EventTeam $eventTeam,
        EventJoinRequest $request,
        $uuid
    ) {

        $user = $eventUserModelService->updateOrCreate(['nfn_user' => $request->get('nfn_user')], ['nfn_user' => $request->get('nfn_user')]);

        if ($user !== null) {
            $team = $eventTeam->with(['event'])->find($request->get('team_id'));
            $team->users()->syncWithoutDetaching([$user->id]);

            \Flash::success(t('Thank you for your registration.'));

            return \Redirect::route('front.events.read', [$team->event->id]);
        }

        \Flash::error(t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));

        return \Redirect::route('front.events.signup', [$uuid]);
    }
}
