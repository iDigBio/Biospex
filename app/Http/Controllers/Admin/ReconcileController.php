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
use App\Jobs\NfnExpertReviewJob;
use App\Jobs\NfnExpertReviewPublishJob;
use App\Services\Api\ZooniverseTalkApiService;
use App\Services\Process\ExpertReconcileProcess;
use Illuminate\Http\RedirectResponse;
use App\Services\Model\ExpeditionService;
use App\Services\Api\PanoptesApiService;
use Flash;

/**
 * Class ReconcileController
 *
 * @package App\Http\Controllers\Admin
 */
class ReconcileController extends Controller
{
    /**
     * @var \App\Services\Process\ExpertReconcileProcess
     */
    private $expertReconcileService;

    /**
     * @var \App\Services\Model\ExpeditionService
     */
    private $expeditionService;

    /**
     * ReconcileController constructor.
     *
     * @param \App\Services\Process\ExpertReconcileProcess $expertReconcileService
     * @param \App\Services\Model\ExpeditionService $expeditionService
     */
    public function __construct(ExpertReconcileProcess $expertReconcileService, ExpeditionService $expeditionService)
    {
        $this->expertReconcileService = $expertReconcileService;
        $this->expeditionService = $expeditionService;
    }

    /**
     * Show files needing reconciliation with pagination.
     *
     * @param string $expeditionId
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @param \App\Services\Api\ZooniverseTalkApiService $zooniverseTalkApiService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(string $expeditionId, PanoptesApiService $panoptesApiService, ZooniverseTalkApiService $zooniverseTalkApiService)
    {
        $expedition = $this->expeditionService->findWith($expeditionId, ['project.group.owner', 'panoptesProject']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        $reconciles = $this->expertReconcileService->getPagination((int) $expedition->id);

        if ($reconciles->isEmpty()) {
            Flash::error(t('Reconcile data for processing is missing.'));

            return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        $comments = $zooniverseTalkApiService->getComments($expedition->panoptesProject->panoptes_project_id, 69205117);

        $location = $panoptesApiService->getSubjectImageLocation($reconciles->first()->subject_id);
        $imgUrl = $this->expertReconcileService->getImageUrl($reconciles->first()->subject_imageName, $location);
        $columns = $this->expertReconcileService->setColumnMasks($reconciles->first()->columns);

        return view('admin.reconcile.index', compact('reconciles', 'columns', 'imgUrl', 'expedition', 'comments'));
    }

    /**
     * Set up data and redirect to index for processing.
     *
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(string $expeditionId): RedirectResponse
    {
        $expedition = $this->expeditionService->findWith($expeditionId, ['project.group.owner']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        NfnExpertReviewJob::dispatch($expedition->id);

        Flash::success(t('The job to create the Expert Review has been submitted. You will receive an email when it is complete and review can begin.'));

        return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expeditionId]);
    }

    /**
     * Update reconciled record.
     *
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(string $expeditionId): RedirectResponse
    {
        if (! $this->expertReconcileService->updateRecord(request()->all())) {
            Flash::warning(t('Error while updating record.'));

            return redirect()->back();
        }

        Flash::success(t('Record was updated successfully.'));

        return redirect()->to(request()->get('page'));
    }

    /**
     * Publish reconciled csv file.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function publish(string $projectId, string $expeditionId): RedirectResponse
    {
        NfnExpertReviewPublishJob::dispatch($expeditionId);
        Flash::success(t('The Expert Review Publish job has been submitted. You will receive and email when it has completed.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }
}
