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

use App\Facades\DateHelper;
use App\Http\Controllers\Controller;
use App\Repositories\WeDigBioEventDateRepository;

class WeDigBioController extends Controller
{

    public function __construct()
    {
        
    }

    /**
     * Index page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(WeDigBioEventDateRepository $weDigBioEventDateRepository)
    {
        $results = $weDigBioEventDateRepository->all()->sortBy('created_at');

        [$events, $eventsCompleted] = $results->partition(function ($event) {
            return $event->active;
        });

        return \View::make('front.wedigbio.index', compact('events', 'eventsCompleted'));
    }
}
