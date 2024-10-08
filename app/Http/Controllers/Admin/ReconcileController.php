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
use App\Repositories\ExpeditionRepository;
use App\Services\Api\PanoptesApiService;
use App\Services\Api\ZooniverseTalkApiService;
use App\Services\Reconcile\ExpertReconcileService;
use App\Services\Reconcile\ReconcileService;
use App\Traits\SkipZooniverse;
use Flash;
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

    private ExpertReconcileService $expertReconcileService;

    private ExpeditionRepository $expeditionRepo;

    private ReconcileService $reconcileService;

    /**
     * ReconcileController constructor.
     */
    public function __construct(
        ExpertReconcileService $expertReconcileService,
        ExpeditionRepository $expeditionRepo,
        ReconcileService $reconcileService
    ) {
        $this->expertReconcileService = $expertReconcileService;
        $this->expeditionRepo = $expeditionRepo;
        $this->reconcileService = $reconcileService;
    }

    /**
     * Show files needing reconciliation with pagination.
     */
    public function index(int $expeditionId, PanoptesApiService $panoptesApiService, ZooniverseTalkApiService $zooniverseTalkApiService): View|RedirectResponse
    {
        $expedition = $this->expeditionRepo->findWith($expeditionId, ['project.group.owner', 'panoptesProject']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        $reconciles = $this->expertReconcileService->getPagination((int) $expedition->id);

        if ($reconciles->isEmpty()) {
            Flash::error(t('Reconcile data for processing is missing.'));

            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
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
        $expedition = $this->expeditionRepo->findWith($expeditionId, ['project.group.owner']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        if ($this->skipReconcile($expeditionId)) {
            Flash::warning(t('Expert Review Process for Expedition (:id) was skipped. Please contact Biospex Administration', [':id' => $expeditionId]));

            return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expeditionId]);
        }

        $this->reconcileService->invokeLambdaExplained($expedition->id);

        Flash::success(t('The job to create the Expert Review has been submitted. You will receive an email when it is complete and review can begin.'));

        return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expeditionId]);
    }

    /**
     * Update reconciled record.
     */
    public function update(int $expeditionId): RedirectResponse
    {
        if (! $this->expertReconcileService->updateRecord(Request::all())) {
            Flash::warning(t('Error while updating record.'));

            return back();
        }

        Flash::success(t('Record was updated successfully.'));

        return Redirect::to(Request::get('page'));
    }

    /**
     * Publish reconciled csv file.
     */
    public function publish(int $projectId, int $expeditionId): RedirectResponse
    {
        ExpertReconcileReviewPublishJob::dispatch($expeditionId);
        Flash::success(t('The Expert Review Publish job has been submitted. You will receive and email when it has completed.'));

        return Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Upload reconciled qc file.
     */
    public function reconciledWithUser(int $projectId, int $expeditionId): View|\Illuminate\Http\JsonResponse
    {
        return $this->reconcileService->reconciledWithUserFile($projectId, $expeditionId);
    }
}
