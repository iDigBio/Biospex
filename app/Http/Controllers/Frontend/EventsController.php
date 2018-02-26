<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\DateHelper;
use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventGroup;
use App\Repositories\Interfaces\EventUser;
use App\Repositories\Interfaces\Project;
use Auth;

class EventsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Event
     */
    private $event;

    /**
     * @var \App\Repositories\Interfaces\EventGroup
     */
    private $eventGroup;

    /**
     * @var \App\Repositories\Interfaces\EventUser
     */
    private $eventUser;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $project;

    /**
     * EventsController constructor.
     *
     * @param \App\Repositories\Interfaces\Event $event
     * @param \App\Repositories\Interfaces\EventGroup $eventGroup
     * @param \App\Repositories\Interfaces\EventUser $eventUser
     * @param \App\Repositories\Interfaces\Project $project
     */
    public function __construct(Event $event, EventGroup $eventGroup, EventUser $eventUser, Project $project)
    {

        $this->event = $event;
        $this->eventGroup = $eventGroup;
        $this->eventUser = $eventUser;
        $this->project = $project;
    }

    public function index()
    {
        $events = $this->event->getUserEvents(Auth::id());
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
        $event = $this->event->getEventShow($eventId);

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
        $event = $this->event->createEvent($request->all());

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
        $event = $this->event->findWith($eventId, ['groups']);
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
        $event = $this->event->updateEvent($request->all(), $eventId);

        if ($event) {
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
        if ($this->event->delete($eventId))
        {
            Flash::success(trans('messages.record_deleted'));

            return redirect()->route('webauth.events.index');
        }

        Flash::error(trans('messages.record_delete_error'));

        return redirect()->route('webauth.events.edit', [$eventId]);
    }
}
