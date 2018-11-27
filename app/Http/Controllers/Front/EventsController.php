<?php

namespace App\Http\Controllers\Front;

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
     * Displays Events on public page.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param null $sort
     * @param null $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Event $eventContract, $sort = null, $order = null)
    {
        $events = $eventContract->getEventPublicPage($sort, $order);

        return request()->ajax() ?
            view('front.event.partials.event', compact('events')) :
            view('front.event.index', compact('events'));
    }

    /**
     * Displays Completed Events on public page.
     *
     * @param \App\Repositories\Interfaces\Event $eventContract
     * @param null $sort
     * @param null $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function completed(Event $eventContract, $sort = null, $order = null)
    {
        $events = $eventContract->getEventCompletedPublicPage($sort, $order);

        return view('front.event.partials.event', compact('events'));
    }

}
