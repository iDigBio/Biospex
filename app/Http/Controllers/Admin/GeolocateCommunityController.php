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
use App\Http\Requests\GeoLocateCommunityRequest;
use App\Models\Expedition;
use App\Services\Actor\GeoLocate\GeoLocateFormService;
use App\Services\Actor\GeoLocate\GeoLocateStatService;
use App\Services\Helpers\GeneralService;
use App\Services\Permission\CheckPermission;
use Redirect;
use Request;
use Response;
use Throwable;
use View;

class GeolocateCommunityController extends Controller
{
    public function __construct(
        protected GeoLocateFormService $geoLocateFormService,
        protected GeoLocateStatService $geoLocateStatService,
        protected GeneralService $generalService) {}

    /**
     * Show community and datasource form in modal.
     */
    public function edit(Expedition $expedition): \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $expedition->load('project.group', 'project.geoLocateCommunities', 'geoLocateDataSource');

        if (! CheckPermission::handle('isOwner', $expedition->project->group)) {
            return Redirect::route('admin.expeditions.show', [$expedition]);
        }

        return View::make('admin.geolocate.partials.community-form-body', compact('expedition'));
    }

    /**
     * Store community and datasource form.
     */
    public function update(GeoLocateCommunityRequest $request, Expedition $expedition): \Illuminate\Http\JsonResponse
    {
        try {

            if (! Request::ajax()) {
                return Response::json(['message' => t('Request must be ajax.')], 400);
            }

            $expedition->load('project.group');

            if (! CheckPermission::handle('updateProject', $expedition->project->group)) {
                return Response::json(['message' => t('You are not authorized for this action.')], 401);
            }

            $this->geoLocateStatService->saveCommunityDataSource($request->all(), $expedition);

            return Response::json(['message' => t('Community and data source added.')]);
        } catch (Throwable $throwable) {
            return Response::json(['message' => t($throwable->getMessage())], 400);
        }
    }
}
