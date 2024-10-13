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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use App\Services\Actor\GeoLocate\GeoLocateExportService;
use App\Services\Actor\GeoLocate\GeoLocateFormService;
use App\Services\Actor\GeoLocate\GeoLocateStatService;
use App\Services\Permission\CheckPermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Redirect;
use Request;
use Response;
use Throwable;

class GeoLocateController extends Controller
{
    /**
     * GeoLocateController constructor.
     */
    public function __construct(
        protected GeoLocateFormService $geoLocateFormService,
        protected GeoLocateStatService $geoLocateStatService,
        protected GeoLocateExportService $geoLocateExportService
    ) {}

    /**
     * Store form data.
     */
    public function store(Expedition $expedition): JsonResponse
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $expedition->load('project.group');

            if (! CheckPermission::handle('updateProject', $expedition->project->group)) {
                return Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            $this->geoLocateFormService->saveForm(Request::all(), $expedition);

            return Response::json(['message' => t('GeoLocate export form saved.')]);
        } catch (Throwable $throwable) {
            return Response::json(['message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * Delete GeoLocateForm and associated data and file.
     */
    public function destroy(Expedition $expedition): JsonResponse|RedirectResponse
    {
        try {
            $expedition->load('project.group');

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return Redirect::route('admin.expeditions.show', [$expedition]);
            }

            $this->geoLocateExportService->destroyGeoLocate($expedition);

            return Redirect::route('admin.expeditions.show', [$expedition])
                ->with('success', t('GeoLocateExport form data and file deleted.'));
        } catch (Throwable $throwable) {
            return Redirect::route('admin.expeditions.show', [$expedition])
                ->with('danger', t('Error %s.', $throwable->getMessage()));
        }
    }
}
