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

use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Jobs\EventTranscriptionExportCsvJob;
use App\Jobs\EventUserExportCsvJob;
use App\Services\EventService;
use App\Services\Models\EventModel;
use App\Services\Models\EventTeamModel;
use App\Services\Models\ProjectModelService;
use Auth;
use Date;
use Illuminate\Support\Facades\View;
use Redirect;

/**
 * Class EventController
 */
class EventController extends Controller
{
    /**
     * EventController constructor.
     */
    public function __construct(
        protected EventService $eventService,

        protected EventModel $eventModel,
        protected EventTeamModel $eventTeamModel,
        protected ProjectModelService $projectModelService
    ) {}

    /**
     * Display events.
     */
    public function index()
    {
        try {
            [$events, $eventsCompleted] = $this->eventService->index(Auth::user());

            return View::make('admin.event.index', compact('events', 'eventsCompleted'));
        } catch (\Throwable $throwable) {

            return Redirect::route('admin.projects.index')->with('error', t('An error occurred when retrieving Event records.'));
        }
    }

    /**
     * Displays Completed Events on public page.
     */
    public function sort(): ?\Illuminate\Contracts\View\View
    {
        if (! \Request::ajax()) {
            return null;
        }

        $results = $this->eventModel->getAdminIndex(Auth::user(), \Request::get('sort'), \Request::get('order'));

        [$active, $completed] = $results->partition(function ($event) {
            return Date::eventBefore($event) || Date::eventActive($event);
        });

        $events = \Request::get('type') === 'active' ? $active : $completed;

        return \View::make('front.event.partials.event', compact('events'));
    }

    /**
     * Show event.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show($eventId)
    {
        $event = $this->eventModel->getShow($eventId);

        if (! $this->checkPermissions('read', $event)) {
            return Redirect::route('admin.events.index');
        }

        return \View::make('admin.event.show', compact('event'));
    }

    /**
     * Create event.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @throws \Exception
     */
    public function create()
    {
        $projects = $this->projectModelService->getProjectEventSelect();
        $timezones = Date::timeZoneSelect();
        $teamsCount = old('entries', 1);

        return \View::make('admin.event.create', compact('projects', 'timezones', 'teamsCount'));
    }

    /**
     * Store Event.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EventFormRequest $request)
    {
        try {
            $data = $this->eventModel->setDates($request->all());
            $event = $this->eventModel->create($data);
            $event->teams()->saveMany($this->eventTeamModel->makeTeams($request->get('teams')));

            return Redirect::route('admin.events.show', [$event->id])->with('success', t('Record was created successfully.'));
        } catch (\Throwable $throwable) {

            return Redirect::route('admin.events.index')->with('error', t('An error occurred when saving record.'));
        }
    }

    /**
     * Edit event.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *
     * @throws \Exception
     */
    public function edit(int $eventId)
    {
        $event = $this->eventModel->getShow($eventId);

        if (! $this->checkPermissions('update', $event)) {
            return back();
        }

        $projects = $this->projectModelService->getProjectEventSelect();
        $timezones = Date::timeZoneSelect();
        $teamsCount = old('entries', $event->teams->count() ?: 1);

        return \View::make('admin.event.edit', compact('event', 'projects', 'timezones', 'teamsCount'));
    }

    /**
     * Update Event.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($eventId, EventFormRequest $request)
    {
        $event = $this->eventModel->findWith($eventId, ['teams']);

        if (! $this->checkPermissions('update', $event)) {
            return Redirect::route('admin.events.index');
        }

        $result = $this->eventModel->updateEvent($request->all(), $eventId);

        if ($result) {
            return Redirect::route('admin.events.show', [$eventId])->with('success', t('Record was updated successfully.'));
        }

        return Redirect::route('admin.events.edit', [$eventId])->with('error', t('Error while updating record.'));
    }

    /**
     * Delete Event.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (! $this->checkPermissions('delete', $event)) {
            return Redirect::route('admin.events.index');
        }

        $result = $event->delete();

        if ($result) {
            return Redirect::route('admin.events.index')->with('success', t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));
        }

        return Redirect::route('admin.events.edit', [$eventId])->with('error', t('An error occurred when deleting record.'));
    }

    /**
     * Export transcription csv from event.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportTranscriptions($eventId)
    {
        if (! \Request::ajax()) {
            return response()->json(false);
        }

        EventTranscriptionExportCsvJob::dispatch(Auth::user(), $eventId);

        return response()->json(true);
    }

    /**
     * Export users csv from event.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportUsers($eventId)
    {
        if (! \Request::ajax()) {
            return response()->json(false);
        }

        EventUserExportCsvJob::dispatch(Auth::user(), $eventId);

        return response()->json(true);
    }
}
