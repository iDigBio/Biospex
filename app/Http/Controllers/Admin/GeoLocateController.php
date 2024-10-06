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
use App\Jobs\GeoLocateStatsJob;
use App\Services\Actor\GeoLocate\GeoLocateExportForm;
use App\Services\Actor\GeoLocate\GeoLocateStat;
use App\Services\Helpers\GeneralService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class GeoLocateController extends Controller
{
    /**
     * GeoLocateController constructor.
     */
    public function __construct(
        protected GeoLocateExportForm $geoLocateExportForm,
        protected GeoLocateStat $geoLocateStat,
        protected GeneralService $generalService
    ) {}

    /**
     * Display stats in modal.
     */
    public function index(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group', 'geoLocateDataSource'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permission.')], 401);
            }

            return \View::make('admin.geolocate.partials.stats', compact('expedition'));
        } catch (Throwable $throwable) {
            return \Response::json(['message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * Show export form in modal.
     */
    public function show(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group.geoLocateForms', 'geoLocateExport'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permission.')], 401);
            }

            $form = $this->geoLocateExportForm->getForm($expedition, ['formId' => $expedition->geo_locate_form_id]);
            $disabled = $form['exported'] && $this->generalService->downloadFileExists($expedition->geoLocateExport->file, $expedition->geoLocateExport->type, $expedition->geoLocateExport->actor_id);

            $formFields = \View::make('admin.geolocate.partials.form-fields', compact('expedition', 'form', 'disabled'))->render();

            $route = route('admin.geolocates.form', [$expedition->project->id, $expedition->id]);

            return \View::make('admin.geolocate.partials.form-show', compact('expedition', 'route', 'formFields'));
        } catch (Throwable $throwable) {
            return \Response::json(['message' => $throwable->getMessage().$throwable->getFile().$throwable->getLine()], 500);
        }
    }

    /**
     * Display export form when select is changed.
     */
    public function form(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            $form = $this->geoLocateExportForm->getForm($expedition, \Request::all());
            $disabled = $form['exported'] && $this->generalService->downloadFileExists($expedition->geoLocateExport->file, $expedition->geoLocateExport->type, $expedition->geoLocateExport->actor_id);

            return \View::make('admin.geolocate.partials.form-fields', compact('expedition', 'form', 'disabled'));
        } catch (Throwable $throwable) {
            return \Response::json(['message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * Retrieve form fields for selected form.
     */
    public function fields(int $projectId, int $expeditionId): \Illuminate\Http\JsonResponse|string
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group.geoLocateForms', 'geoLocateExport'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permission.')], 401);
            }

            $form = $this->geoLocateExportForm->getForm($expedition, [
                'formId' => $expedition->geo_locate_form_id,
                'source' => \Request::input('source'),
            ]);

            return \View::make('admin.geolocate.partials.geolocate-fields', compact('expedition', 'form'))->render();
        } catch (Throwable $throwable) {
            return \Response::json(['message' => $throwable->getMessage().$throwable->getFile().$throwable->getLine()], 500);
        }
    }

    /**
     * Store form data.
     */
    public function store(int $projectId, int $expeditionId): \Illuminate\Http\JsonResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {
            $relations = ['project.group'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            $this->geoLocateExportForm->saveForm(\Request::all(), $expedition);

            return \Response::json(['message' => t('GeoLocate export form saved.')]);
        } catch (Throwable $throwable) {
            return \Response::json(['message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * Export form selections to csv.
     */
    public function export(int $projectId, int $expeditionId): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('Request must be ajax.')], 400);
        }

        try {

            $relations = ['project.group'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            GeoLocateExportJob::dispatch($expedition, Auth::user());

            return \Response::json(['message' => t('Geo Locate export job scheduled for processing. You will receive an email when file has been created.')]);
        } catch (Throwable $throwable) {
            return \Response::json(['message' => $throwable->getMessage()], 500);
        }
    }

    /**
     * Refresh Geo Locate stats at user request.
     */
    public function refresh(int $projectId, int $expeditionId): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        try {
            $relations = ['project.group', 'geoLocateActor'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            GeoLocateStatsJob::dispatch($expedition->geoLocateActor, true);

            return \Redirect::route('admin.expeditions.show', [
                $projectId,
                $expeditionId,
            ])->with('success', t('Geo Locate stats job is scheduled for processing. You will receive an email when the process is complete.'));
        } catch (Throwable $throwable) {
            return \Redirect::route('admin.expeditions.show', [
                $projectId,
                $expeditionId,
            ])->with('danger', t('An error occurred while processing job.'));
        }
    }

    /**
     * Delete GeoLocateForm and associated data and file.
     */
    public function delete(int $projectId, int $expeditionId): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        try {
            $relations = ['project.group'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $this->geoLocateExportForm->deleteGeoLocateFile($expeditionId);
            $this->geoLocateExportForm->deleteGeoLocate($expeditionId);

            $expedition->geoLocateForm()->dissociate()->save();
            $expedition->actors()->updateExistingPivot(config('geolocate.actor_id'), [
                'state' => 0,
            ]);

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId])->with('success', t('GeoLocateExport form data and file deleted.'));
        } catch (Throwable $throwablexception) {

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId])->with('danger', t('Error %s.', $exception->getMessage()));
        }
    }

    /**
     * Show community and datasource form in modal.
     */
    public function communityForm(
        int $projectId,
        int $expeditionId
    ): View|\Illuminate\Http\JsonResponse|RedirectResponse {
        if (! \Request::ajax()) {
            return \Response::json(['message' => t('You do not have permission.')], 400);
        }

        $relations = ['project.group', 'project.geoLocateCommunity', 'geoLocateDataSource'];
        $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

        if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        return \View::make('admin.geolocate.partials.community-form-body', compact('expedition'));
    }

    /**
     * Store community and datasource form.
     */
    public function community(
        GeoLocateCommunityRequest $request,
        int $projectId,
        int $expeditionId
    ): \Illuminate\Http\JsonResponse {
        try {

            if (! \Request::ajax()) {
                return \Response::json(['message' => t('Request must be ajax.')], 400);
            }

            $relations = ['project.group'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You are not authorized for this action.')], 401);
            }

            $this->geoLocateStat->saveCommunityDataSource(\Request::all(), $projectId, $expeditionId);

            $expedition->actors()->updateExistingPivot(config('geolocate.actor_id'), [
                'state' => 2,
            ]);

            return \Response::json(['message' => t('Community and data source added.')]);
        } catch (\Throwable $throwable) {
            return \Response::json(['message' => t($throwable->getMessage())], 400);
        }
    }
}
