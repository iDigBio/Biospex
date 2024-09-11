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

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Event\EventService;
use App\Services\Models\EventModel;
use Illuminate\Support\Facades\View;

/**
 * Class EventController
 */
class EventController extends Controller
{
    public function __construct(protected EventService $eventService, protected EventModel $eventModel) {}

    /**
     * Displays Events on public page.
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        [$events, $eventsCompleted] = $this->eventService->getPublicIndex();

        return View::make('front.event.index', compact('events', 'eventsCompleted'));
    }

    /**
     * Display the show page for an event.
     */
    public function show(Event $event): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $event->load(['project.lastPanoptesProject', 'teams:id,title,event_id']);

        return View::make('front.event.show', compact('event'));
    }
}
