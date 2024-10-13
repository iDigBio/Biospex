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
use App\Jobs\GeoLocateStatsJob;
use App\Models\Expedition;
use App\Services\Permission\CheckPermission;
use Illuminate\Http\RedirectResponse;
use Redirect;
use Request;
use Response;
use Throwable;
use View;

class GeoLocateStatController extends Controller
{
    /**
     * Display stats in modal.
     */
    public function index(Expedition $expedition): mixed
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $expedition->load('project.group', 'geoLocateDataSource');

            if (! CheckPermission::handle('readProject', $expedition->project->group)) {
                return Response::json(['message' => t('You do not have permission.')], 401);
            }

            return View::make('admin.geolocate.partials.stats', compact('expedition'));
        } catch (Throwable $throwable) {
            return Response::json(['message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * Refresh GeoLocate stats at user request.
     */
    public function update(Expedition $expedition): RedirectResponse
    {
        try {
            $expedition->load('project.group');

            if (! CheckPermission::handle('updateProject', $expedition->project->group)) {
                return Redirect::route('admin.expeditions.show', [$expedition]);
            }

            GeoLocateStatsJob::dispatch($expedition, true);

            return Redirect::route('admin.expeditions.show', [$expedition])
                ->with('success', t('GeoLocate stats job is scheduled for processing. You will receive an email when the process is complete.'));
        } catch (Throwable $throwable) {
            return Redirect::route('admin.expeditions.show', [$expedition])
                ->with('danger', t('An error occurred while processing job.'));
        }
    }
}
