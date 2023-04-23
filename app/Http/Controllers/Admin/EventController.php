<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Admin;

use App\Facades\DateHelper;
use App\Facades\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Jobs\EventTranscriptionExportCsvJob;
use App\Jobs\EventUserExportCsvJob;
use App\Repositories\EventRepository;
use App\Repositories\ProjectRepository;
use Auth;
use Flash;

/**
 * Class EventController
 *
 * @package App\Http\Controllers\Admin
 */
class EventController extends Controller
{
    /**
     * @var \App\Repositories\EventRepository
     */
    private EventRepository $eventRepo;

    /**
     * EventController constructor.
     *
     * @param \App\Repositories\EventRepository $eventRepo
     */
    public function __construct(EventRepository $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    /**
     * Displays Events on public page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $results = $this->eventRepo->getEventAdminIndex(Auth::user());

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return DateHelper::eventBefore($event) || DateHelper::eventActive($event);
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

        $results = $this->eventRepo->getEventPublicIndex(request()->get('sort'), request()->get('order'));

        [$active, $completed] = $results->partition(function ($event) {
            return DateHelper::eventBefore($event) || DateHelper::eventActive($event);
        });

        $events = request()->get('type') === 'active' ? $active : $completed;

        return view('front.event.partials.event', compact('events'));
    }

    /**
     * Show event.
     *
     * @param $eventId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show($eventId)
    {
        $event = $this->eventRepo->getEventShow($eventId);

        if ( ! $this->checkPermissions('read', $event))
        {
            return redirect()->route('admin.events.index');
        }

        return view('admin.event.show', compact('event'));
    }

    /**
     * Create event.
     *
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function create(ProjectRepository $projectRepo)
    {
        $projects = $projectRepo->getProjectEventSelect();
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
        $event = $this->eventRepo->createEvent($request->all());

        if ($event) {
            Flash::success(t('Record was created successfully.'));

            return redirect()->route('admin.events.show', [$event->id]);
        }

        Flash::error(t('An error occurred when saving record.'));

        return redirect()->route('admin.events.index');
    }

    /**
     * Edit event.
     *
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @param $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function edit(ProjectRepository $projectRepo, $eventId)
    {
        $event = $this->eventRepo->findWith($eventId, ['teams']);

        if ( ! $this->checkPermissions('update', $event))
        {
            return redirect()->back();
        }

        $projects = $projectRepo->getProjectEventSelect();
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
        $event = $this->eventRepo->findWith($eventId, ['teams']);

        if ( ! $this->checkPermissions('update', $event))
        {
            return redirect()->route('admin.events.index');
        }

        $result = $this->eventRepo->updateEvent($request->all(), $eventId);

        if ($result) {
            Flash::success(t('Record was updated successfully.'));

            return redirect()->route('admin.events.show', [$eventId]);
        }

        Flash::error(t('Error while updating record.'));

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
        $event = $this->eventRepo->find($eventId);

        if ( ! $this->checkPermissions('delete', $event))
        {
            return redirect()->route('admin.events.index');
        }

        $result = $event->delete();

        if ($result)
        {
            Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

            return redirect()->route('admin.events.index');
        }

        Flash::error(t('An error occurred when deleting record.'));

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

        EventTranscriptionExportCsvJob::dispatch(Auth::user(), $eventId);

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

        EventUserExportCsvJob::dispatch(Auth::user(), $eventId);

        return response()->json(true);
    }
}
