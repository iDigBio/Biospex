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
use App\Jobs\GeoLocateExportJob;
use App\Models\Expedition;
use App\Services\Permission\CheckPermission;
use Auth;
use Request;
use Response;
use Throwable;

class GeolocateExportController extends Controller
{
    /**
     * Export form selections to csv.
     */
    public function __invoke(Expedition $expedition): \Illuminate\Http\JsonResponse
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $expedition->load('project.group');

            if (! CheckPermission::handle('updateProject', $expedition->project->group)) {
                return Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            GeoLocateExportJob::dispatch($expedition, Auth::user());

            return Response::json(['message' => t('GeoLocate export job scheduled for processing. You will receive an email when file has been created.')]);
        } catch (Throwable $throwable) {
            return Response::json(['message' => $throwable->getMessage()], 500);
        }
    }
}
