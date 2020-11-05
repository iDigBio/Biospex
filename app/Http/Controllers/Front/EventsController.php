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

use Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Services\Model\EventService;
use App\Services\Model\EventTeamService;
use App\Services\Model\EventUserService;
use GeneralHelper;

/**
 * Class EventsController
 *
 * @package App\Http\Controllers\Front
 */
class EventsController extends Controller
{
    /**
     * Displays Events on public page.
     *
     * @param \App\Services\Model\EventService $eventService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(EventService $eventService)
    {
        $results = $eventService->getEventPublicIndex();

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event);
        });

        return view('front.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     *
     * @param \App\Services\Model\EventService $eventService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort(EventService $eventService)
    {
        if (! request()->ajax()) {
            return null;
        }

        $sort = request()->get('sort');
        $order = request()->get('order');
        $projectId = request()->get('id');

        $results = $eventService->getEventPublicIndex($sort, $order, $projectId);

        [$active, $completed] = $results->partition(function ($event) {
            return GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event);
        });

        $events = request()->get('type') === 'active' ? $active : $completed;

        return view('front.event.partials.event', compact('events'));
    }

    /**
     * Display the show page for an event.
     *
     * @param \App\Services\Model\EventService $eventService
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function read(EventService $eventService, $eventId)
    {
        $event = $eventService->findWith($eventId, ['project.lastPanoptesProject', 'teams:id,title,event_id']);

        if ($event === null) {
            Flash::error(t('Error retrieving record from database'));

            return redirect()->route('front.events.index');
        }

        return view('front.event.show', compact('event'));
    }

    /**
     * Group join page for events.
     *
     * @param \App\Services\Model\EventTeamService $eventTeamService
     * @param $uuid
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function signup(EventTeamService $eventTeamService, $uuid)
    {
        $team = $eventTeamService->getTeamByUuid($uuid);

        $active = GeneralHelper::eventBefore($team->event) || GeneralHelper::eventActive($team->event);

        if ($team === null) {
            Flash::error(t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
        }

        return view('front.event.signup', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
     * @param \App\Services\Model\EventUserService $eventUserService
     * @param \App\Services\Model\EventTeamService $eventTeamService
     * @param \App\Http\Requests\EventJoinRequest $request
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(
        EventUserService $eventUserService,
        EventTeamService $eventTeamService,
        EventJoinRequest $request,
        $uuid
    ) {

        $user = $eventUserService->updateOrCreate(['nfn_user' => $request->get('nfn_user')], ['nfn_user' => $request->get('nfn_user')]);

        if ($user !== null) {
            $team = $eventTeamService->findWith($request->get('team_id'), ['event']);
            $team->users()->syncWithoutDetaching([$user->id]);

            Flash::success(t('Thank you for your registration.'));

            return redirect()->route('front.events.read', [$team->event->id]);
        }

        Flash::error(t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));

        return redirect()->route('front.events.signup', [$uuid]);
    }
}
