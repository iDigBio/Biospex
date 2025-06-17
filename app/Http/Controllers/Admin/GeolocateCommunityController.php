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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GeoLocateCommunityRequest;
use App\Models\Expedition;
use App\Models\GeoLocateCommunity;
use App\Services\Actor\GeoLocate\GeoLocateFormService;
use App\Services\Actor\GeoLocate\GeoLocateStatService;
use App\Services\Helpers\GeneralService;
use App\Services\Permission\CheckPermission;
use Redirect;
use Request;
use Response;
use Throwable;
use View;

/**
 * Class GeolocateCommunityController
 *
 * This controller handles the display and updating of community
 * and datasource forms related to expeditions. It ensures proper
 * permissions and request handling for AJAX calls.
 */
class GeolocateCommunityController extends Controller
{
    /**
     * Constructor method for initializing services.
     *
     * @param  GeoLocateFormService  $geoLocateFormService  The service responsible for handling geolocation form-related operations.
     * @param  GeoLocateStatService  $geoLocateStatService  The service responsible for handling geolocation statistics operations.
     * @param  GeneralService  $generalService  The general service used for common functionalities.
     * @return void
     */
    public function __construct(
        protected GeoLocateFormService $geoLocateFormService,
        protected GeoLocateStatService $geoLocateStatService,
        protected GeneralService $generalService) {}

    /**
     * Handles the editing process for an expedition.
     *
     * @param  Expedition  $expedition  The expedition instance to be edited, including related loaded data and permission checks.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     *                                                                                               Returns a view containing the expedition edit form if the request is valid and permissions are granted.
     *                                                                                               Returns a JSON response with an error message if the request is not made via AJAX.
     *                                                                                               Redirects to the expedition details page if the user lacks proper permissions.
     */
    public function edit(Expedition $expedition): \Illuminate\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if (! Request::ajax()) {
            return Response::json(['message' => t('Request must be ajax.')], 400);
        }

        $expedition->load('project.group', 'project.geoLocateCommunities.geoLocateDataSources', 'geoLocateDataSource');

        if (! CheckPermission::handle('isOwner', $expedition->project->group)) {
            return Redirect::route('admin.expeditions.show', [$expedition]);
        }

        $communities = $expedition->project->geoLocateCommunities;

        return View::make('admin.geolocate.partials.community-form-body', compact('expedition', 'communities'));
    }

    /**
     * Updates the community data source for a given expedition based on the request data.
     *
     * @param  GeoLocateCommunityRequest  $request  The request object containing community data and source information.
     * @param  Expedition  $expedition  The expedition being updated with the provided community data.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the status of the operation.
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

    /**
     * Deletes the specified community if conditions are met.
     *
     * @param  GeoLocateCommunity  $community  The community model instance to be deleted.
     * @return \Illuminate\Http\RedirectResponse A redirect response with a success or error message.
     */
    public function destroy(GeoLocateCommunity $community)
    {
        try {
            $community->load('project.group', 'geoLocateDataSources');

            if (! CheckPermission::handle('updateProject', $community->project->group)) {
                return Redirect::route('admin.projects.show', [$community->project])
                    ->with('danger', t('You are not authorized for this action.'));
            }

            if ($community->geoLocateDataSources->count() > 0) {
                return Redirect::route('admin.projects.show', [$community->project])
                    ->with('danger', t('You cannot delete a community that has data sources attached.'));
            }

            $community->delete();

            return Redirect::route('admin.projects.show', [$community->project])
                ->with('success', t('Record was deleted successfully.'));
        } catch (Throwable $throwable) {
            return Redirect::route('admin.projects.show', [$community->project])
                ->with('danger', t('Error %s.', $throwable->getMessage()));
        }
    }
}
