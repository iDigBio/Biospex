<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
use App\Models\WeDigBioEvent;
use App\Services\WeDigBio\WeDigBioService;
use Request;
use View;

class WeDigBioProgressController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected WeDigBioService $weDigBioService) {}

    /**
     * Show progress for wedigbio events.
     */
    public function __invoke(?WeDigBioEvent $event = null): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if (! Request::ajax()) {
            return response()->json(['html' => 'Error retrieving the WeDigBio Event']);
        }

        $weDigBioEvent = $this->weDigBioService->getWeDigBioEventTranscriptions($event);

        return View::make('common.wedigbio-progress-content', compact('weDigBioEvent'));
    }
}
