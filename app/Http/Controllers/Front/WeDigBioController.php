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
use App\Services\Models\WeDigBioEventDateModelService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class WeDigBioController extends Controller
{
    public function __construct(protected WeDigBioEventDateModelService $weDigBioEventDateModelService) {}

    /**
     * Index page.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Contracts\View\View
     */
    public function __invoke(): mixed
    {
        if (! Auth::check()) {
            return Redirect::route('front.projects.index')->with('info', 'You must be logged in to access this page.');
        }

        $results = $this->weDigBioEventDateModelService->all()->sortBy('created_at');

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return $event->active;
        });

        return View::make('front.wedigbio.index', compact('events', 'eventsCompleted'));
    }
}
