<?php

namespace App\Http\Controllers\Front;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventTeam;
use App\Repositories\Interfaces\EventUser;
use Illuminate\Support\Carbon;

class EventsController extends Controller
{
    /**
     * Displays Events on public page.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Event $eventContract)
    {
        $results = $eventContract->getEventPublicIndex();

        list($events, $eventsCompleted) = $results->partition(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);

            return $now->between($start_date, $end_date);
        });

        return view('front.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort(Event $eventContract)
    {
        if (! request()->ajax()) {
            return null;
        }

        $sort = request()->get('sort');
        $order = request()->get('order');
        $projectId = request()->get('id');

        $results = $eventContract->getEventPublicIndex($sort, $order, $projectId);

        list($active, $completed) = $results->partition(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);

            return $now->between($start_date, $end_date);
        });

        $events = request()->get('type') === 'active' ? $active : $completed;

        return view('front.event.partials.event', compact('events'));
    }

    public function read(Event $contract, $eventId)
    {
        $event = $contract->findWith($eventId, ['project']);

        return view('front.event.show', compact('event'));
    }

    /**
     * Group join page for events.
     *
     * @param \App\Repositories\Interfaces\EventTeam $eventTeamContract
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function signup(EventTeam $eventTeamContract, $uuid)
    {
        $team = $eventTeamContract->getTeamByUuid($uuid);

        $start_date = $team->event->start_date->setTimezone($team->event->timezone);
        $end_date = $team->event->end_date->setTimezone($team->event->timezone);
        $now = Carbon::now($team->event->timezone);
        $active = $now->between($start_date, $end_date);

        if ($team === null) {
            FlashHelper::error(trans('messages.event_join_team_error'));
        }

        return view('front.event.signup', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
     * @param \App\Repositories\Interfaces\EventUser $eventUserContract
     * @param \App\Repositories\Interfaces\EventTeam $eventTeamContract
     * @param \App\Http\Requests\EventJoinRequest $request
     * @param $uuid
     * @return \Illuminate\Http\RedirectResponse
     */
    public function join(
        EventUser $eventUserContract,
        EventTeam $eventTeamContract,
        EventJoinRequest $request,
        $uuid
    ) {

        $user = $eventUserContract->updateOrCreate(['nfn_user' => $request->get('nfn_user')], ['nfn_user' => $request->get('nfn_user')]);

        if ($user !== null) {
            $team = $eventTeamContract->find($request->get('team_id'));
            $team->users()->save($user);

            FlashHelper::success(trans('messages.event_join_team_success'));

            return redirect()->route('front.events.signup', [$uuid]);
        }

        FlashHelper::error(trans('messages.event_join_team_error'));

        return redirect()->route('front.events.signup', [$uuid]);
    }
}
