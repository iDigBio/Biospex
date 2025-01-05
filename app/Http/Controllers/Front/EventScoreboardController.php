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
use App\Models\Event;
use App\Services\Event\EventScoreboardService;
use Request;
use View;

class EventScoreboardController extends Controller
{
    /**
     * EventScoreboardController constructor.
     */
    public function __construct(protected EventScoreboardService $eventScoreboardService) {}

    /**
     * Load event scoreboard.
     */
    public function __invoke(Event $event): \Illuminate\Contracts\View\View
    {
        if (! Request::ajax() || ! isset($event->uuid)) {
            $event = null;
        }

        $this->eventScoreboardService->getEventScoreboard($event);

        return View::make('common.scoreboard-content', ['event' => $event]);
    }
}
