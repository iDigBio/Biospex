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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Event\EventService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class EventSortController extends Controller
{
    /**
     * Displays Completed Events on public page.
     */
    public function index(EventService $eventService): ?\Illuminate\Contracts\View\View
    {
        if (! Request::ajax()) {
            return null;
        }

        [$eventsActive, $eventsCompleted] = $eventService->getAdminIndex(Auth::user(), Request::all());

        $events = Request::get('type') === 'active' ? $eventsActive : $eventsCompleted;

        return View::make('front.event.partials.event', compact('events'));
    }
}
