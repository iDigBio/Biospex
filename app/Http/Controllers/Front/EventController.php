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

use App\Facades\DateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Repositories\EventRepository;
use App\Repositories\EventTeamRepository;
use App\Repositories\EventUserRepository;

/**
 * Class EventController
 */
class EventController extends Controller
{
    /**
     * Displays Events on public page.
     */
    public function index(EventRepository $eventRepo): \Illuminate\Contracts\View\View
    {
        $results = $eventRepo->getEventPublicIndex();
        $project = $results->first()->project;

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return DateHelper::eventBefore($event) || DateHelper::eventActive($event);
        });

        return \View::make('front.event.index', compact('events', 'project', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     */
    public function sort(EventRepository $eventRepo): ?\Illuminate\Contracts\View\View
    {
        if (! \Request::ajax()) {
            return null;
        }

        $sort = \Request::get('sort');
        $order = \Request::get('order');
        $projectId = \Request::get('id');

        $results = $eventRepo->getEventPublicIndex($sort, $order, $projectId);

        [$active, $completed] = $results->partition(function ($event) {
            return DateHelper::eventBefore($event) || DateHelper::eventActive($event);
        });

        $events = \Request::get('type') === 'active' ? $active : $completed;

        return \View::make('front.event.partials.event', compact('events'));
    }

    /**
     * Display the show page for an event.
     */
    public function read(EventRepository $eventRepo, $eventId): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $event = $eventRepo->findWith($eventId, ['project.lastPanoptesProject', 'teams:id,title,event_id']);

        if ($event === null) {
            \Flash::error(t('Error retrieving record from database'));

            return \Redirect::route('front.events.index');
        }

        return \View::make('front.event.show', compact('event'));
    }

    /**
     * Group join page for events.
     *
     * @throws \Exception
     */
    public function signup(EventTeamRepository $eventTeamRepo, $uuid): \Illuminate\Contracts\View\View
    {
        $team = $eventTeamRepo->getTeamByUuid($uuid);

        $active = DateHelper::eventBefore($team->event) || DateHelper::eventActive($team->event);

        if ($team === null) {
            \Flash::error(t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
        }

        return \View::make('front.event.signup', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(
        EventUserRepository $eventUserRepo,
        EventTeamRepository $eventTeamRepo,
        EventJoinRequest $request,
        $uuid
    ) {

        $user = $eventUserRepo->updateOrCreate(['nfn_user' => $request->get('nfn_user')], ['nfn_user' => $request->get('nfn_user')]);

        if ($user !== null) {
            $team = $eventTeamRepo->findWith($request->get('team_id'), ['event']);
            $team->users()->syncWithoutDetaching([$user->id]);

            \Flash::success(t('Thank you for your registration.'));

            return \Redirect::route('front.events.read', [$team->event->id]);
        }

        \Flash::error(t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));

        return \Redirect::route('front.events.signup', [$uuid]);
    }
}
