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
use App\Jobs\ExpertReviewMigrateReconcilesJob;
use App\Jobs\ExpertReviewProcessExplainedJob;
use App\Jobs\ExpertReviewSetProblemsJob;
use App\Repositories\ExpeditionRepository;
use App\Services\Api\PanoptesApiService;
use App\Services\Api\ZooniverseTalkApiService;
use App\Services\Reconcile\ExpertReconcileProcess;
use Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Bus;
use Redirect;
use Request;

/**
 * Class ReconcileController
 *
 * @package App\Http\Controllers\Admin
 */
class ReconcileController extends Controller
{
    /**
     * @var \App\Services\Reconcile\ExpertReconcileProcess
     */
    private ExpertReconcileProcess $expertreconcileRepo;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepo;

    /**
     * ReconcileController constructor.
     *
     * @param \App\Services\Reconcile\ExpertReconcileProcess $expertreconcileRepo
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     */
    public function __construct(ExpertReconcileProcess $expertreconcileRepo, ExpeditionRepository $expeditionRepo)
    {
        $this->expertreconcileRepo = $expertreconcileRepo;
        $this->expeditionRepo = $expeditionRepo;
    }

    /**
     * Show files needing reconciliation with pagination.
     *
     * @param int $expeditionId
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @param \App\Services\Api\ZooniverseTalkApiService $zooniverseTalkApiService
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(int $expeditionId, PanoptesApiService $panoptesApiService, ZooniverseTalkApiService $zooniverseTalkApiService): View|RedirectResponse
    {
        $expedition = $this->expeditionRepo->findWith($expeditionId, ['project.group.owner', 'panoptesProject']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        $reconciles = $this->expertreconcileRepo->getPagination((int) $expedition->id);

        if ($reconciles->isEmpty()) {
            Flash::error(t('Reconcile data for processing is missing.'));

            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        $comments = $zooniverseTalkApiService->getComments($expedition->panoptesProject->panoptes_project_id, $reconciles->first()->subject_id);

        $location = $panoptesApiService->getSubjectImageLocation($reconciles->first()->subject_id);
        $imgUrl = $this->expertreconcileRepo->getImageUrl($reconciles->first()->subject_imageName, $location);
        $columns = explode('|', $reconciles->first()->subject_columns);

        return \View::make('admin.reconcile.index', compact('reconciles', 'columns', 'imgUrl', 'expedition', 'comments'));
    }

    /**
     * Set up data and redirect to index for processing.
     *
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function create(int $expeditionId): RedirectResponse
    {
        $expedition = $this->expeditionRepo->findWith($expeditionId, ['project.group.owner']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        Bus::batch([
            new ExpertReviewProcessExplainedJob($expeditionId),
            new ExpertReviewMigrateReconcilesJob($expeditionId),
            new ExpertReviewSetProblemsJob($expeditionId)
        ])->name('Expert Reconcile ' . $expedition->id)->onQueue(config('config.queue.reconcile'))->dispatch();

        Flash::success(t('The job to create the Expert Review has been submitted. You will receive an email when it is complete and review can begin.'));

        return Redirect::route('admin.expeditions.show', [$expedition->project_id, $expeditionId]);
    }

    /**
     * Update reconciled record.
     *
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(int $expeditionId): RedirectResponse
    {
        if (! $this->expertreconcileRepo->updateRecord(Request::all())) {
            Flash::warning(t('Error while updating record.'));

            return back();
        }

        Flash::success(t('Record was updated successfully.'));

        return Redirect::to(Request::get('page'));
    }

    /**
     * Publish reconciled csv file.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish(int $projectId, int $expeditionId): RedirectResponse
    {
        ExpertReconcileReviewPublishJob::dispatch($expeditionId);
        Flash::success(t('The Expert Review Publish job has been submitted. You will receive and email when it has completed.'));

        return Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Upload reconciled qc file.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function reconciledQcFile(int $projectId, int $expeditionId)
    {
        if (Request::isMethod('get')) {
            return \View::make('admin.reconcile.partials.qc-file-upload', compact('projectId', 'expeditionId'));
        }

        if (! request()->hasFile('file') || request()->file('file')->getClientOriginalExtension() !== 'csv') {
            return response()->json(['message' => t('File must be a csv file.')]);
        }

        if (\Storage::disk('s3')->exists(config('zooniverse.directory.reconciledqc').'/'.$expeditionId.'.csv')) {
            \Storage::disk('s3')->delete(config('zooniverse.directory.reconciledqc').'/'.$expeditionId.'.csv');
        }

        if (\Storage::disk('s3')->put(config('zooniverse.directory.reconciledqc').'/'.$expeditionId.'.csv', file_get_contents(request()->file('file')->getRealPath()))) {

            return response()->json(['message' => t('File uploaded.')]);
        }

        return response()->json(['message' => t('Error uploading file.')]);
    }
}
