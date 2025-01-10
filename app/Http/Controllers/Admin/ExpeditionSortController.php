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
use App\Services\Expedition\ExpeditionService;
use Auth;
use Request;
use Response;
use View;

class ExpeditionSortController extends Controller
{
    /**
     * Sort expedition admin page.
     */
    public function __invoke(ExpeditionService $expeditionService): \Illuminate\Http\JsonResponse|\Illuminate\View\View
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        [$active, $completed] = $expeditionService->getAdminIndex(Auth::user(), Request::all());

        $expeditions = Request::get('type') === 'active' ? $active : $completed;

        return View::make('admin.expedition.partials.expedition', compact('expeditions'));
    }
}
