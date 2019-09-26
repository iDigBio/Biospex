<?php

namespace App\Http\Controllers\Front;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventJoinRequest;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventTeam;
use App\Repositories\Interfaces\EventUser;
use GeneralHelper;
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
            return GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event);
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
            return GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event);
        });

        $events = request()->get('type') === 'active' ? $active : $completed;

        return view('front.event.partials.event', compact('events'));
    }

    /**
     * Display the show page for an event.
     *
     * @param \App\Repositories\Interfaces\Event $contract
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function read(Event $contract, $eventId)
    {
        $event = $contract->findWith($eventId, ['project.lastPanoptesProject']);

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

        $active = GeneralHelper::eventBefore($team->event) || GeneralHelper::eventActive($team->event);

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
            $team = $eventTeamContract->findWith($request->get('team_id'), ['event']);
            $team->users()->syncWithoutDetaching([$user->id]);

            FlashHelper::success(trans('messages.event_join_team_success'));

            return redirect()->route('front.events.read', [$team->event->id]);
        }

        FlashHelper::error(trans('messages.event_join_team_error'));

        return redirect()->route('front.events.signup', [$uuid]);
    }
}
