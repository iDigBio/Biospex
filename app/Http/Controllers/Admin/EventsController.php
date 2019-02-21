<?php

namespace App\Http\Controllers\Admin;

use App\Facades\DateHelper;
use App\Facades\FlashHelper;
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
     * Displays Events on public page.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Event $eventContract)
    {
        $results = $eventContract->getEventAdminIndex(Auth::id());

        list($events, $eventsCompleted) = $results->partition(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);

            return $now->between($start_date, $end_date);
        });

        return view('admin.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort(Event $eventContract)
    {
        if ( ! request()->ajax()) {
            return null;
        }

        $results = $eventContract->getEventPublicIndex(request()->get('sort'), request()->get('order'));

        list($active, $completed) = $results->partition(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);

            return $now->between($start_date, $end_date);
        });

        $events = request()->get('type') === 'active' ? $active : $completed;

        return view('front.event.partials.event', compact('events'));
    }

    /**
     * Show event.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Event $eventContract, $eventId)
    {
        $event = $eventContract->getEventShow($eventId);

        if ( ! $this->checkPermissions('read', $event))
        {
            return redirect()->route('admin.events.index');
        }

        return view('admin.event.show', compact('event'));
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
            FlashHelper::success(trans('messages.record_created'));

            return redirect()->route('admin.events.show', [$event->id]);
        }

        FlashHelper::error(trans('messages.record_save_error'));

        return redirect()->route('admin.events.index');
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
            return redirect()->route('admin.events.index');
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
            return redirect()->route('admin.events.index');
        }

        $result = $this->eventContract->updateEvent($request->all(), $eventId);

        if ($result) {
            FlashHelper::success(trans('messages.record_updated'));

            return redirect()->route('admin.events.show', [$eventId]);
        }

        FlashHelper::error(trans('messages.record_updated_error'));

        return redirect()->route('admin.events.edit', [$eventId]);
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
            return redirect()->route('admin.events.index');
        }

        $result = $this->eventContract->delete($event);

        if ($result)
        {
            FlashHelper::success(trans('messages.record_deleted'));

            return redirect()->route('admin.events.index');
        }

        FlashHelper::error(trans('messages.record_delete_error'));

        return redirect()->route('admin.events.edit', [$eventId]);
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
        FlashHelper::success(trans('messages.event_export_success'));

        return redirect()->route('admin.events.show', [$eventId]);
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
        FlashHelper::success(trans('messages.event_export_success'));

        return redirect()->route('admin.events.show', [$eventId]);
    }
}
