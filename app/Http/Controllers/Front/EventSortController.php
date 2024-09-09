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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\EventService;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class EventSortController extends Controller
{
    /**
     * EventSortController constructor.
     */
    public function __construct(protected EventService $eventService) {}

    /**
     * Sort events for public index.
     */
    public function __invoke()
    {
        if (! Request::ajax()) {
            return null;
        }

        [$active, $completed] = $this->eventService->getPublicIndex(Request::all());

        $events = Request::get('type') === 'active' ? $active : $completed;

        return View::make('front.event.partials.event', compact('events'));
    }
}
