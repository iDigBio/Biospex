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

use General;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeoLocateCommunityRequest;
use App\Jobs\GeoLocateExportJob;
use App\Jobs\GeoLocateStatsJob;
use App\Services\Actor\GeoLocate\GeoLocateExportForm;
use App\Services\Actor\GeoLocate\GeoLocateStat;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class GeoLocateController extends Controller
{
    /**
     * @var \App\Services\Actor\GeoLocate\GeoLocateExportForm
     */
    private GeoLocateExportForm $geoLocateExportForm;

    /**
     * @var \App\Services\Actor\GeoLocate\GeoLocateStat
     */
    private GeoLocateStat $geoLocateStat;

    /**
     * GeoLocateController constructor.
     *
     * @param \App\Services\Actor\GeoLocate\GeoLocateExportForm $geoLocateExportForm
     * @param \App\Services\Actor\GeoLocate\GeoLocateStat $geoLocateStat
     */
    public function __construct(
        GeoLocateExportForm $geoLocateExportForm,
        GeoLocateStat $geoLocateStat

    ) {
        $this->geoLocateExportForm = $geoLocateExportForm;
        $this->geoLocateStat = $geoLocateStat;
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
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

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
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permission.')], 401);
            }

            $form = $this->geoLocateExportForm->getForm($expedition, ['formId' => $expedition->geo_locate_form_id]);
            $disabled = $form['exported'] && General::downloadFileExists($expedition->geoLocateExport->file, $expedition->geoLocateExport->type, $expedition->geoLocateExport->actor_id);

            $formFields = \View::make('admin.geolocate.partials.form-fields', compact('expedition', 'form', 'disabled'))->render();

            $route = route('admin.geolocates.form', [$expedition->project->id, $expedition->id]);

            return \View::make('admin.geolocate.partials.form-show', compact('expedition', 'route', 'formFields'));
        } catch (\Exception $e) {
            return \Response::json(['message' => $e->getMessage().$e->getFile().$e->getLine()], 500);
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
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            $form = $this->geoLocateExportForm->getForm($expedition, \Request::all());
            $disabled = $form['exported'] && General::downloadFileExists($expedition->geoLocateExport->file, $expedition->geoLocateExport->type, $expedition->geoLocateExport->actor_id);

            return \View::make('admin.geolocate.partials.form-fields', compact('expedition', 'form', 'disabled'));
        } catch (\Exception $e) {
            return \Response::json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Retrieve form fields for selected form.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\JsonResponse|string
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
        } catch (\Exception $e) {
            return \Response::json(['message' => $e->getMessage().$e->getFile().$e->getLine()], 500);
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
            $relations = ['project.group'];
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return \Response::json(['message' => t('You do not have permissions for this action.')], 401);
            }

            $this->geoLocateExportForm->saveForm(\Request::all(), $expedition);

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
            $expedition = $this->geoLocateExportForm->findExpeditionWithRelations($expeditionId, $relations);

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
     * Refresh Geo Locate stats at user request.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
        } catch (Exception $e) {
            return \Redirect::route('admin.expeditions.show', [
                $projectId,
                $expeditionId,
            ])->with('error', t('An error occurred while processing job.'));
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
        } catch (Exception $exception) {

            return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId])->with('error', t('Error %s.', $exception->getMessage()));
        }
    }

    /**
     * Show community and datasource form in modal.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
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
