<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\DateHelper;
use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Jobs\EventTranscriptionExportCsvJob;
use App\Jobs\EventUserExportCsvJob;
use App\Repositories\Interfaces\Project;
use App\Services\Model\EventService;
use Auth;

class EventsController extends Controller
{
    /**
     * @var \App\Services\Model\EventService
     */
    private $eventService;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $project;

    /**
     * EventsController constructor.
     *
     * @param \App\Services\Model\EventService $eventService
     * @param \App\Repositories\Interfaces\Project $project
     */
    public function __construct(EventService $eventService, Project $project)
    {
        $this->eventService = $eventService;
        $this->project = $project;
    }

    /**
     * Get index page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $events = $this->eventService->getIndex();
        return view('frontend.events.index', compact('events'));
    }

    /**
     * Show event.
     *
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($eventId)
    {
        $event = $this->eventService->getShow($eventId);
        if ( ! $this->checkPermissions('read', $event))
        {
            return redirect()->route('webauth.events.index');
        }

        return view('frontend.events.show', compact('event'));
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

        return view('frontend.events.create', compact('projects', 'timezones'));
    }

    /**
     * Store Event.
     *
     * @param \App\Http\Requests\EventFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EventFormRequest $request)
    {
        $event = $this->eventService->storeEvent($request->all());

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
        $event = $this->eventService->editEvent($eventId);
        if ( ! $this->checkPermissions('update', $event))
        {
            return redirect()->route('webauth.events.index');
        }

        $projects = $this->project->getProjectEventSelect();
        $timezones = DateHelper::timeZoneSelect();

        return view('frontend.events.edit', compact('event', 'projects', 'timezones'));
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
        $event = $this->eventService->findEvent($eventId);
        if ( ! $this->checkPermissions('update', $event))
        {
            return redirect()->route('webauth.events.index');
        }

        $result = $this->eventService->updateEvent($request->all(), $eventId);

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
        $event = $this->eventService->findEvent($eventId);
        if ( ! $this->checkPermissions('delete', $event))
        {
            return redirect()->route('webauth.events.index');
        }

        $result = $this->eventService->deleteEvent($event);

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
    public function exportTranscriptsion($eventId)
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
}
