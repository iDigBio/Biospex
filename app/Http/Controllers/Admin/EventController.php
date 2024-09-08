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
use App\Models\Event;
use App\Services\EventService;
use App\Services\Models\ProjectModelService;
use App\Services\Permission\CheckPermission;
use Illuminate\Support\Facades\Auth;
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
        protected ProjectModelService $projectModelService
    ) {}

    /**
     * Display events.
     */
    public function index()
    {
        try {
            [$events, $eventsCompleted] = $this->eventService->getAdminIndex(Auth::user());

            return View::make('admin.event.index', compact('events', 'eventsCompleted'));
        } catch (\Throwable $throwable) {

            return Redirect::route('admin.projects.index')->with('error', t('An error occurred when retrieving Event records.'));
        }
    }

    /**
     * Show event.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(Event $event)
    {
        $this->eventService->getAdminShow($event);

        if (! CheckPermission::handle('read', $event)) {
            return Redirect::route('admin.events.index');
        }

        return View::make('admin.event.show', compact('event'));
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

        return View::make('admin.event.create', compact('projects'));
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
    public function edit(Event $event)
    {
        $this->eventService->edit($event);

        if (! CheckPermission::handle('update', $event)) {
            return back();
        }

        $projects = $this->projectModelService->getProjectEventSelect();

        return View::make('admin.event.edit', compact('event', 'projects'));
    }

    /**
     * Update Event.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Event $event, EventFormRequest $request)
    {
        if (! CheckPermission::handle('update', $event)) {
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
    public function destroy($eventId)
    {
        $event = $this->eventModel->find($eventId);

        if (! CheckPermission::handle('delete', $event)) {
            return Redirect::route('admin.events.index');
        }

        $result = $event->delete();

        if ($result) {
            return Redirect::route('admin.events.index')->with('success', t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));
        }

        return Redirect::route('admin.events.edit', [$eventId])->with('error', t('An error occurred when deleting record.'));
    }
}
