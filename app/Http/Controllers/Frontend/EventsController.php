<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Repositories\Interfaces\Event;
use App\Repositories\Interfaces\EventGroup;
use App\Repositories\Interfaces\EventUser;
use App\Repositories\Interfaces\Project;

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
        return view('frontend.events.index');
    }

    public function show()
    {

    }

    public function create()
    {
        $projects = $this->project->getProjectEventSelect();

        return view('frontend.events.create', compact('projects'));
    }

    /**
     * Store Event.
     *
     * @param \App\Http\Requests\EventFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EventFormRequest $request)
    {
        $event = $this->event->create($request->all());

        if ($event) {
            Flash::success(trans('messages.record_created'));

            return redirect()->route('webauth.events.show', [$event->id]);
        }

        Flash::error(trans('messages.record_save_error'));

        return redirect()->route('webauth.events.index');
    }

    public function edit($eventId, EventFormRequest $request)
    {
        dd('test');
    }

    public function update()
    {

    }

    public function delete()
    {

    }
}
