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

use Date;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Jobs\EventTranscriptionExportCsvJob;
use App\Jobs\EventUserExportCsvJob;
use App\Services\Models\EventModelService;
use App\Services\Models\ProjectModelService;
use Auth;

/**
 * Class EventController
 *
 * @package App\Http\Controllers\Admin
 */
class EventController extends Controller
{
    /**
     * EventController constructor.
     *
     */
    public function __construct(private EventModelService $eventModelService, private ProjectModelService $projectModelService)
    {}

    /**
     * Displays Events on public page.
     *
     * @return \Illuminate\View\View
     */
    public function index(): \Illuminate\View\View
    {
        $results = $this->eventModelService->getEventAdminIndex(Auth::user());

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return Date::eventBefore($event) || Date::eventActive($event);
        });

        return \View::make('admin.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     *
     * @return \Illuminate\Contracts\View\View|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function sort(): ?\Illuminate\Contracts\View\View
    {
        if ( ! \Request::ajax()) {
            return null;
        }

        $results = $this->eventModelService->getEventAdminIndex(Auth::user(), \Request::get('sort'), \Request::get('order'));

        [$active, $completed] = $results->partition(function ($event) {
            return Date::eventBefore($event) || Date::eventActive($event);
        });

        $events = \Request::get('type') === 'active' ? $active : $completed;

        return \View::make('front.event.partials.event', compact('events'));
    }

    /**
     * Show event.
     *
     * @param $eventId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show($eventId)
    {
        $event = $this->eventModelService->getEventShow($eventId);

        if ( ! $this->checkPermissions('read', $event))
        {
            return \Redirect::route('admin.events.index');
        }

        return \View::make('admin.event.show', compact('event'));
    }

    /**
     * Create event.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
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
     * @param \App\Http\Requests\EventFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(EventFormRequest $request)
    {
        $event = $this->eventModelService->createEvent($request->all());

        if ($event) {
            \Flash::success(t('Record was created successfully.'));

            return \Redirect::route('admin.events.show', [$event->id]);
        }

        \Flash::error(t('An error occurred when saving record.'));

        return \Redirect::route('admin.events.index');
    }

    /**
     * Edit event.
     *
     * @param int $eventId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function edit(int $eventId)
    {
        $event = $this->eventModelService->getEventShow($eventId);

        if ( ! $this->checkPermissions('update', $event))
        {
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
     * @param $eventId
     * @param \App\Http\Requests\EventFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($eventId, EventFormRequest $request)
    {
        $event = $this->eventModelService->findEventWithRelations($eventId, ['teams']);

        if ( ! $this->checkPermissions('update', $event))
        {
            return \Redirect::route('admin.events.index');
        }

        $result = $this->eventModelService->updateEvent($request->all(), $eventId);

        if ($result) {
            \Flash::success(t('Record was updated successfully.'));

            return \Redirect::route('admin.events.show', [$eventId]);
        }

        \Flash::error(t('Error while updating record.'));

        return \Redirect::route('admin.events.edit', [$eventId]);
    }

    /**
     * Delete Event.
     *
     * @param $eventId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($eventId)
    {
        $event = $this->eventModelService->findEventWithRelations($eventId);

        if ( ! $this->checkPermissions('delete', $event))
        {
            return \Redirect::route('admin.events.index');
        }

        $result = $event->delete();

        if ($result)
        {
            \Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

            return \Redirect::route('admin.events.index');
        }

        \Flash::error(t('An error occurred when deleting record.'));

        return \Redirect::route('admin.events.edit', [$eventId]);
    }

    /**
     * Export transcription csv from event.
     *
     * @param $eventId
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportTranscriptions($eventId)
    {
        if ( ! \Request::ajax()) {
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
        if ( ! \Request::ajax()) {
            return response()->json(false);
        }

        EventUserExportCsvJob::dispatch(Auth::user(), $eventId);

        return response()->json(true);
    }
}
