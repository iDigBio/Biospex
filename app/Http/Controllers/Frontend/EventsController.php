<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Repositories\Interfaces\EventTeam;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventUser;
use Illuminate\Support\Carbon;

class EventsController extends Controller
{
    /**
     * Get index page.
     *
     * @param \App\Repositories\Interfaces\Event $contract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Event $contract)
    {
        $events = $contract->getEvents();
        return view('frontend.events.index', compact('events'));
    }

    /**
     * Group join page for events.
     *
     * @param $uuid
     * @param \App\Repositories\Interfaces\EventTeam $contract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function eventJoin($uuid, EventTeam $contract)
    {
        $team = $contract->getTeamByUuid($uuid);

        $start_date = $team->event->start_date->setTimezone($team->event->timezone);
        $end_date = $team->event->end_date->setTimezone($team->event->timezone);
        $now = Carbon::now($team->event->timezone);
        $active = $now->between($start_date, $end_date);

        if ($team === null) {
            Flash::error(trans('messages.event_join_team_error'));
        }

        return view('events.get.join', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
     * @param \App\Http\Requests\EventJoinRequest $request
     * @param \App\Repositories\Interfaces\EventUser $userContract
     * @param \App\Repositories\Interfaces\EventTeam $teamContract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function eventJoinCreate(EventJoinRequest $request, EventUser $userContract, EventTeam $teamContract)
    {
        $user = $userContract->updateOrCreate(['nfn_user' => $request->get('nfn_user')], ['nfn_user' => $request->get('nfn_user')]);

        if ($user !== null) {
            $team = $teamContract->find($request->get('team_id'));
            $team->users()->save($user);

            Flash::success(trans('messages.event_join_team_success'));
            return redirect()->route('web.events.join', [$request->get('uuid')]);
        }

        Flash::error(trans('messages.event_join_team_error'));

        return redirect()->route('web.events.join', [$request->get('uuid')]);
    }
}
