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
use App\Http\Requests\GeoLocateCommunityRequest;
use App\Jobs\GeoLocateExportJob;
use App\Services\GeoLocate\ExportFormService;
use App\Services\GeoLocate\StatService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class GeoLocateController extends Controller
{
    /**
     * @var \App\Services\GeoLocate\ExportFormService
     */
    private ExportFormService $exportFormService;

    /**
     * @var \App\Services\GeoLocate\StatService
     */
    private StatService $statService;

    /**
     * GeoLocateController constructor.
     *
     * @param \App\Services\GeoLocate\ExportFormService $exportFormService
     * @param \App\Services\GeoLocate\StatService $statService
     */
    public function __construct(
        ExportFormService $exportFormService,
        StatService $statService

    )
    {
        $this->exportFormService = $exportFormService;
        $this->statService = $statService;
    }

    /**
     * Display stats in modal.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group', 'geoLocateDataSource'];
            $expedition = $this->exportFormService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permission.')], 401);
            }

            return \View::make('admin.geolocate.partials.stats', compact('expedition'));
        } catch (\Exception $e) {
            return \Response::json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show export form in modal.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group.geoLocateForms', 'geoLocateExport'];
            $expedition = $this->exportFormService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permission.')], 401);
            }

            $form = $this->exportFormService->getForm($expedition, ['formId' => $expedition->geo_locate_form_id]);

            $formFields = \View::make('admin.geolocate.partials.form-fields', compact('expedition', 'form'))->render();

            $route = route('admin.geolocates.form', [$expedition->project->id, $expedition->id]);

            return \View::make('admin.geolocate.partials.form-show', compact('expedition', 'route', 'formFields'));
        } catch (\Exception $e) {
            return \Response::json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display export form when select is changed.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function form(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group'];
            $expedition = $this->exportFormService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            $form = $this->exportFormService->getForm($expedition, \Request::all());

            return \View::make('admin.geolocate.partials.form-fields', compact('expedition', 'form'));
        } catch (\Exception $e) {
            return \Response::json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store form data.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(int $projectId, int $expeditionId): \Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $data = [];
            parse_str(\Request::input('data'), $data);

            $relations = ['project.group'];
            $expedition = $this->exportFormService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            $this->exportFormService->saveForm($data, $expedition);

            return \Response::json(['message' => t('GeoLocate export form saved.')]);

        } catch (Exception $e) {
            return \Response::json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Export form selections to csv.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function export(int $projectId, int $expeditionId): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {

            $relations = ['project.group'];
            $expedition = $this->exportFormService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            GeoLocateExportJob::dispatch($expedition, Auth::user());

            return \Response::json(['message' => t('Geo Locate export job scheduled for processing. You will receive an email when file has been created.')]);

        } catch (Exception $e) {
            return \Response::json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete GeoLocateForm and associated data and file.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function delete(int $projectId, int $expeditionId): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group'];
            $expedition = $this->exportFormService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $this->exportFormService->deleteGeoLocateFile($expeditionId);
            $this->exportFormService->deleteGeoLocate($expeditionId);

            $expedition->geoLocateForm()->dissociate()->save();
            $expedition->actors()->updateExistingPivot(config('config.geolocate.actor_id'), [
                'state' => 0,
            ]);

            \Flash::success(t('GeoLocateExport form data and file deleted.'));

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
        } catch (Exception $exception) {
            \Flash::error(t('Error %s.', $exception->getMessage()));

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Show community and datasource form in modal.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function communityForm(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse|RedirectResponse
    {
        if (!\Request::ajax()) {
            return \Response::json(['message' => t('You do not have permission.')], 400);
        }

        $relations = ['project.group', 'project.geoLocateCommunity', 'geoLocateDataSource'];
        $expedition = $this->exportFormService->findExpeditionWithRelations($expeditionId, $relations);

        if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        return \View::make('admin.geolocate.partials.community-form-body', compact('expedition'));
    }

    /**
     * Store community and datasource form.
     *
     * @param \App\Http\Requests\GeoLocateCommunityRequest $request
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function community(
        GeoLocateCommunityRequest $request,
        int $projectId,
        int $expeditionId
    ): \Illuminate\Http\JsonResponse {
        try {
            $data = [];
            parse_str(\Request::input('data'), $data);

            if (! \Request::ajax()) {
                return \Response::json(['message' => t('Request must be ajax.')], 400);
            }

            $relations = ['project.group'];
            $expedition = $this->exportFormService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You are not authorized for this action.')], 401);
            }

            $this->statService->saveCommunityDataSource($data, $projectId, $expeditionId);

            $expedition->actors()->updateExistingPivot(config('config.geolocate.actor_id'), [
                'state' => 2,
            ]);

            return \Response::json(['message' => t('Community and data source added.')]);

        } catch (\Throwable $throwable) {
            return \Response::json(['message' => t($throwable->getMessage())],400 );
        }
    }
}
