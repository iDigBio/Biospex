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

use App\Facades\DateHelper as Date;
use App\Facades\GeneralHelper as General;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Models\EventTeam;
use App\Services\EventService;
use App\Services\Models\EventModel;
use App\Services\Models\EventUserModelService;
use Redirect;
use Request;
use Session;
use View;

/**
 * Class EventController
 */
class EventController extends Controller
{
    public function __construct(protected EventService $eventService, protected EventModel $eventModel) {}

    /**
     * Displays Events on public page.
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $results = $this->eventModel->getPublicIndex();

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return Date::eventBefore($event) || Date::eventActive($event);
        });

        return View::make('front.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     */
    public function sort(): ?\Illuminate\Contracts\View\View
    {
        if (! Request::ajax()) {
            return null;
        }

        $sort = Request::get('sort');
        $order = Request::get('order');
        $projectId = Request::get('id');

        $results = $this->eventModel->getPublicIndex($sort, $order, $projectId);

        [$active, $completed] = $results->partition(function ($event) {
            return Date::eventBefore($event) || Date::eventActive($event);
        });

        $events = Request::get('type') === 'active' ? $active : $completed;

        return View::make('front.event.partials.event', compact('events'));
    }

    /**
     * Display the show page for an event.
     */
    public function read(int $eventId): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $event = $this->eventModel->findWith($eventId, ['project.lastPanoptesProject', 'teams:id,title,event_id']);

        if ($event === null) {

            return Redirect::route('front.events.index')->with('danger', t('Error retrieving record from database'));
        }

        return View::make('front.event.show', compact('event'));
    }

    /**
     * Group join page for events.
     */
    public function signup(EventTeam $eventTeam, $uuid): \Illuminate\Contracts\View\View
    {
        $team = $eventTeam->with(['event'])->where('uuid', General::uuidToBin($uuid))->first();

        $active = Date::eventBefore($team->event) || Date::eventActive($team->event);

        if ($team === null) {
            Session::flash('error', t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
        }

        return View::make('front.event.signup', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
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

            return Redirect::route('front.events.read', [$team->event->id])->with('success', t('Thank you for your registration.'));
        }

        return Redirect::route('front.events.signup', [$uuid])
            ->with('danger', t('The event team could not be found. Please check you are using the correct link or contact event coordinator.'));
    }
}
