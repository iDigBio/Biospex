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
use App\Http\Requests\Request;
use App\Services\Models\EventModel;
use Illuminate\Contracts\View\Factory as View;

class EventScoreboardController extends Controller
{
    /**
     * EventScoreboardController constructor.
     *
     * @param \App\Http\Requests\Request $request
     * @param \Illuminate\Contracts\View\Factory $view
     * @param \App\Services\Models\EventModel $eventModel
     */
    public function __construct(protected Request $request, protected View $view, protected EventModel $eventModel)
    {}

    /**
     * Load event scoreboard.
     *
     * @param int $eventId
     * @return \Illuminate\Contracts\View\View
     */
    public function show(int $eventId)
    {
        $event = $this->eventModel->getEventScoreboard($eventId, ['id']);

        if (! $this->request->ajax() || is_null($event)) {
            $event = null;
        }

        return $this->view->make('common.scoreboard-content', ['event' => $event]);
    }
}
