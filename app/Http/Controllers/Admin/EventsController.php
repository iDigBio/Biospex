<?php

namespace App\Http\Controllers\Admin;

use App\Facades\DateHelper;
use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Jobs\EventTranscriptionExportCsvJob;
use App\Jobs\EventUserExportCsvJob;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\Project;
use Auth;
use Illuminate\Support\Carbon;

class EventsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Event
     */
    private $eventContract;

    /**
     * EventsController constructor.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     */
    public function __construct(
        Event $eventContract
    )
    {
        $this->eventContract = $eventContract;
    }

    /**
     * Displays Events on public page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $results = $this->eventContract->getEventAdminIndex(Auth::id());

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort()
    {
        if ( ! request()->ajax()) {
            return null;
        }

        $results = $this->eventContract->getEventPublicIndex(request()->get('sort'), request()->get('order'));

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
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($eventId)
    {
        $event = $this->eventContract->getEventShow($eventId);

        if ( ! $this->checkPermissions('read', $event))
        {
            return redirect()->route('admin.events.index');
        }

        return view('admin.event.show', compact('event'));
    }

    /**
     * Create event.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function create(Project $projectContract)
    {
        $projects = $projectContract->getProjectEventSelect();
        $timezones = DateHelper::timeZoneSelect();
        $teamsCount = old('entries', 1);

        return view('admin.event.create', compact('projects', 'timezones', 'teamsCount'));
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
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function edit(Project $projectContract, $eventId)
    {
        $event = $this->eventContract->findWith($eventId, ['teams']);

        if ( ! $this->checkPermissions('update', $event))
        {
            return redirect()->back();
        }

        $projects = $projectContract->getProjectEventSelect();
        $timezones = DateHelper::timeZoneSelect();
        $teamsCount = old('entries', $event->teams->count() ?: 1);

        return view('admin.event.edit', compact('event', 'projects', 'timezones', 'teamsCount'));
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportTranscriptions($eventId)
    {
        if ( ! request()->ajax()) {
            return response()->json(false);
        }

        EventTranscriptionExportCsvJob::dispatch(\Auth::user(), $eventId);

        return response()->json(true);
    }

    /**
     * Export users csv from event.
     *
     * @param $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportUsers($eventId)
    {
        if ( ! request()->ajax()) {
            return response()->json(false);
        }

        EventUserExportCsvJob::dispatch(\Auth::user(), $eventId);

        return response()->json(true);
    }
}