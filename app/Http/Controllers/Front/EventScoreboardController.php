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
use App\Services\Models\EventModel;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\View;

class EventScoreboardController extends Controller
{
    /**
     * EventScoreboardController constructor.
     */
    public function __construct(protected EventService $eventService, protected EventModel $eventModel) {}

    /**
     * Load event scoreboard.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function show(int $eventId)
    {
        $event = $this->eventService->getEventScoreboard($eventId, ['id']);

        if (! Request::ajax() || is_null($event)) {
            $event = null;
        }

        return View::make('common.scoreboard-content', ['event' => $event]);
    }
}