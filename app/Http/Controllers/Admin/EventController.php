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
use App\Http\Controllers\Controller;
use App\Http\Requests\EventFormRequest;
use App\Jobs\EventTranscriptionExportCsvJob;
use App\Jobs\EventUserExportCsvJob;
use App\Repositories\EventRepository;
use App\Repositories\ProjectRepository;
use Auth;

/**
 * Class EventController
 */
class EventController extends Controller
{
    private EventRepository $eventRepo;

    /**
     * EventController constructor.
     */
    public function __construct(EventRepository $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    /**
     * Displays Events on public page.
     */
    public function index(): \Illuminate\View\View
    {
        $results = $this->eventRepo->getEventAdminIndex(Auth::user());

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return DateHelper::eventBefore($event) || DateHelper::eventActive($event);
        });

        return \View::make('admin.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Displays Completed Events on public page.
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function sort(): ?\Illuminate\Contracts\View\View
    {
        if (! \Request::ajax()) {
            return null;
        }

        $results = $this->eventRepo->getEventPublicIndex(\Request::get('sort'), \Request::get('order'));

        [$active, $completed] = $results->partition(function ($event) {
            return DateHelper::eventBefore($event) || DateHelper::eventActive($event);
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
        $event = $this->eventRepo->getEventShow($eventId);

        if (! $this->checkPermissions('read', $event)) {
            return \Redirect::route('admin.events.index');
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
    public function create(ProjectRepository $projectRepo)
    {
        $projects = $projectRepo->getProjectEventSelect();
        $timezones = DateHelper::timeZoneSelect();
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
        $event = $this->eventRepo->createEvent($request->all());

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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *
     * @throws \Exception
     */
    public function edit(ProjectRepository $projectRepo, $eventId)
    {
        $event = $this->eventRepo->getEventShow($eventId);

        if (! $this->checkPermissions('update', $event)) {
            return back();
        }

        $projects = $projectRepo->getProjectEventSelect();
        $timezones = DateHelper::timeZoneSelect();
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
        $event = $this->eventRepo->findWith($eventId, ['teams']);

        if (! $this->checkPermissions('update', $event)) {
            return \Redirect::route('admin.events.index');
        }

        $result = $this->eventRepo->updateEvent($request->all(), $eventId);

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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($eventId)
    {
        $event = $this->eventRepo->find($eventId);

        if (! $this->checkPermissions('delete', $event)) {
            return \Redirect::route('admin.events.index');
        }

        $result = $event->delete();

        if ($result) {
            \Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

            return \Redirect::route('admin.events.index');
        }

        \Flash::error(t('An error occurred when deleting record.'));

        return \Redirect::route('admin.events.edit', [$eventId]);
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
