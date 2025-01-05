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
use App\Jobs\GridExportCsvJob;
use App\Models\Expedition;
use Auth;
use Request;
use Response;

class ExpeditionGridExportController extends Controller
{
    /**
     * Export csv from grid button.
     */
    public function __invoke(Expedition $expedition): \Illuminate\Http\JsonResponse
    {
        $attributes = [
            'projectId' => $expedition->project_id,
            'expeditionId' => $expedition->id,
            'postData' => ['filters' => Request::exists('filters') ? Request::get('filters') : null],
            'route' => Request::get('route'),
        ];

        GridExportCsvJob::dispatch(Auth::user(), $attributes);

        return Response::json(['success' => true], 200);
    }
}
