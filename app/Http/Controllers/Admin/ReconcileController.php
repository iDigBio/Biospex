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
use App\Jobs\ExpertReconcileReviewPublishJob;
use App\Services\Api\PanoptesApiService;
use App\Services\Api\ZooniverseTalkApiService;
use App\Services\Models\ExpeditionModelService;
use App\Services\Reconcile\ExpertReconcileService;
use App\Services\Reconcile\ReconcileService;
use App\Traits\SkipZooniverse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Request;

/**
 * Class ReconcileController
 */
class ReconcileController extends Controller
{
    use SkipZooniverse;

    /**
     * ReconcileController constructor.
     */
    public function __construct(
        private ExpertReconcileService $expertReconcileService,
        private ExpeditionModelService $expeditionModelService,
        private ReconcileService $reconcileService
    ) {}

    /**
     * Show files needing reconciliation with pagination.
     */
    public function index(int $expeditionId, PanoptesApiService $panoptesApiService, ZooniverseTalkApiService $zooniverseTalkApiService): View|RedirectResponse
    {
        $expedition = $this->expeditionModelService->findExpeditionWithRelations($expeditionId, ['project.group.owner', 'panoptesProject']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        $reconciles = $this->expertReconcileService->getPagination((int) $expedition->id);

        if ($reconciles->isEmpty()) {

            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id])
                ->with('error', t('Reconcile data for processing is missing.'));
        }

        $comments = $zooniverseTalkApiService->getComments($expedition->panoptesProject->panoptes_project_id, $reconciles->first()->subject_id);

        $location = $panoptesApiService->getSubjectImageLocation($reconciles->first()->subject_id);
        $imgUrl = $this->expertReconcileService->getImageUrl($reconciles->first()->subject_imageName, $location);
        $columns = explode('|', $reconciles->first()->subject_columns);

        return \View::make('admin.reconcile.index', compact('reconciles', 'columns', 'imgUrl', 'expedition', 'comments'));
    }

    /**
     * Start Expert Review set up by invoking explained via lambda labelReconciliations
     * and redirect to index for processing.
     *
     * @throws \Throwable
     */
    public function create(int $expeditionId): RedirectResponse
    {
        $expedition = $this->expeditionModelService->findExpeditionWithRelations($expeditionId, ['project.group.owner']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        if ($this->skipReconcile($expeditionId)) {

            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expeditionId])
                ->with('warning', t('Expert Review Process for Expedition (:id) was skipped. Please contact Biospex Administration', [':id' => $expeditionId]));
        }

        $this->reconcileService->invokeLambdaExplained($expedition->id);

        return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expeditionId])
            ->with('success', t('The job to create the Expert Review has been submitted. You will receive an email when it is complete and review can begin.'));
    }

    /**
     * Update reconciled record.
     */
    public function update(int $expeditionId): RedirectResponse
    {
        if (! $this->expertReconcileService->updateRecord(Request::all())) {

            return Redirect::back()->with('error', t('Error while updating record.'));
        }

        return Redirect::to(Request::get('page'))->with('success', t('Record was updated successfully.'));
    }

    /**
     * Publish reconciled csv file.
     */
    public function publish(int $projectId, int $expeditionId): RedirectResponse
    {
        ExpertReconcileReviewPublishJob::dispatch($expeditionId);

        return Redirect::route('admin.expeditions.show', [$projectId, $expeditionId])
            ->with('success', t('The Expert Review Publish job has been submitted. You will receive an email when it has completed.'));
    }

    /**
     * Upload reconciled qc file.
     */
    public function reconciledWithUser(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse
    {
        return $this->reconcileService->reconciledWithUserFile($projectId, $expeditionId);
    }
}
