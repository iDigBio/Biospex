<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\DateHelper;
use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Http\Requests\EventJoinRequest;
use App\Jobs\EventTranscriptionExportCsvJob;
use App\Jobs\EventUserExportCsvJob;
use App\Repositories\Interfaces\EventTeam;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventUser;
use App\Repositories\Interfaces\Project;
use Auth;
use Illuminate\Support\Carbon;

class EventsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $project;

    /**
     * @var \App\Repositories\Interfaces\Event
     */
    private $eventContract;

    /**
     * @var \App\Models\EventTeam
     */
    private $eventTeamContract;

    /**
     * @var \App\Repositories\Interfaces\EventUser
     */
    private $eventUserContract;

    /**
     * EventsController constructor.
     *
     * @param \App\Repositories\Interfaces\Project $project
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param \App\Models\EventTeam $eventTeamContract
     * @param \App\Repositories\Interfaces\EventUser $eventUserContract
     */
    public function __construct(
        Project $project,
        Event $eventContract,
        EventTeam $eventTeamContract,
        EventUser $eventUserContract
    )
    {
        $this->project = $project;
        $this->eventContract = $eventContract;
        $this->eventTeamContract = $eventTeamContract;
        $this->eventUserContract = $eventUserContract;
    }

    /**
     * Get index page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $events = $this->eventContract->getUserEvents(Auth::id());
        return view('front.events.index', compact('events'));
    }

    /**
     * Show event.
     *
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($eventId)
    {
        $event = $this->eventContract->getEventShow($eventId);

        if ( ! $this->checkPermissions('read', $event))
        {
            return redirect()->route('webauth.events.index');
        }

        return view('front.events.show', compact('event'));
    }

    /**
     * Create event.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $projects = $this->project->getProjectEventSelect();
        $timezones = DateHelper::timeZoneSelect();

        return view('front.events.create', compact('projects', 'timezones'));
    }

    /**
     * Store Event.
     *
     * @param \App\Http\Requests\EventFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EventFormRequest $request)
    {
        $event = $this->eventContract->createEvent($request->all());

        if ($event) {
            Flash::success(trans('messages.record_created'));

            return redirect()->route('webauth.events.show', [$event->id]);
        }

        Flash::error(trans('messages.record_save_error'));

        return redirect()->route('webauth.events.index');
    }

    /**
     * Edit event.
     *
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($eventId)
    {
        $event = $this->eventContract->findWith($eventId, ['teams']);

        if ( ! $this->checkPermissions('update', $event))
        {
            return redirect()->route('webauth.events.index');
        }

        $projects = $this->project->getProjectEventSelect();
        $timezones = DateHelper::timeZoneSelect();

        return view('front.events.edit', compact('event', 'projects', 'timezones'));
    }

    /**
     * Update Event.
     *
     * @param $eventId
     * @param \App\Http\Requests\EventFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($eventId, EventFormRequest $request)
    {
        $event = $this->eventContract->findWith($eventId, ['teams']);

        if ( ! $this->checkPermissions('update', $event))
        {
            return redirect()->route('webauth.events.index');
        }

        $result = $this->eventContract->updateEvent($request->all(), $eventId);

        if ($result) {
            Flash::success(trans('messages.record_updated'));

            return redirect()->route('webauth.events.show', [$eventId]);
        }

        Flash::error(trans('messages.record_updated_error'));

        return redirect()->route('webauth.events.edit', [$eventId]);
    }

    /**
     * Delete Event.
     *
     * @param $eventId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($eventId)
    {
        $event = $this->eventContract->find($eventId);

        if ( ! $this->checkPermissions('delete', $event))
        {
            return redirect()->route('webauth.events.index');
        }

        $result = $this->eventContract->delete($event);

        if ($result)
        {
            Flash::success(trans('messages.record_deleted'));

            return redirect()->route('webauth.events.index');
        }

        Flash::error(trans('messages.record_delete_error'));

        return redirect()->route('webauth.events.edit', [$eventId]);
    }

    /**
     * Export transcription csv from event.
     *
     * @param $eventId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function exportTranscriptions($eventId)
    {
        EventTranscriptionExportCsvJob::dispatch(\Auth::user(), $eventId);
        Flash::success(trans('messages.event_export_success'));

        return redirect()->route('webauth.events.show', [$eventId]);
    }

    /**
     * Export users csv from event.
     *
     * @param $eventId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function exportUsers($eventId)
    {
        EventUserExportCsvJob::dispatch(\Auth::user(), $eventId);
        Flash::success(trans('messages.event_export_success'));

        return redirect()->route('webauth.events.show', [$eventId]);
    }

    /**
     * Group join page for events.
     *
     * @param $uuid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function eventJoin($uuid)
    {
        $team = $this->eventTeamContract->getTeamByUuid($uuid);

        $start_date = $team->event->start_date->setTimezone($team->event->timezone);
        $end_date = $team->event->end_date->setTimezone($team->event->timezone);
        $now = Carbon::now($team->event->timezone);
        $active = $now->between($start_date, $end_date);

        if ($team === null) {
            Flash::error(trans('messages.event_join_team_error'));
        }

        return view('front.events.join', compact('team', 'active'));
    }

    /**
     * Store user for event group.
     *
     * @param \App\Http\Requests\EventJoinRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function eventJoinCreate(EventJoinRequest $request)
    {
        $user = $this->eventUserContract->updateOrCreate(['nfn_user' => $request->get('nfn_user')], ['nfn_user' => $request->get('nfn_user')]);

        if ($user !== null) {
            $team = $this->eventTeamContract->find($request->get('team_id'));
            $team->users()->save($user);

            Flash::success(trans('messages.event_join_team_success'));
            return redirect()->route('web.events.join', [$request->get('uuid')]);
        }

        Flash::error(trans('messages.event_join_team_error'));

        return redirect()->route('web.events.join', [$request->get('uuid')]);
    }
}
