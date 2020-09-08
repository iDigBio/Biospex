<?php
/**
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
use App\Services\Model\ReconcileService;
use Illuminate\Http\RedirectResponse;
use App\Repositories\Interfaces\Expedition;
use App\Services\Api\PanoptesApiService;
use Flash;

class ReconcilesController extends Controller
{
    /**
     * @var \App\Services\Model\ReconcileService
     */
    private $service;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * ReconcilesController constructor.
     *
     * @param \App\Services\Model\ReconcileService $service
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     */
    public function __construct(ReconcileService $service, Expedition $expeditionContract)
    {
        $this->service = $service;
        $this->expeditionContract = $expeditionContract;
    }

    /**
     * Show files needing reconciliation with pagination.
     *
     * @param string $expeditionId
     * @param \App\Services\Api\PanoptesApiService $panoptesApiService
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(string $expeditionId, PanoptesApiService $panoptesApiService)
    {
        $expedition = $this->expeditionContract->findWith($expeditionId, ['project.group.owner']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        $reconciles = $this->service->getPagination((int) $expedition->id);

        if ($reconciles->isEmpty()) {
            Flash::error(t('Reconcile data for processing is missing.'));

            return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        $location = $panoptesApiService->getSubjectImageLocation($reconciles->first()->subject_id);
        $imgUrl = $this->service->getImageUrl($reconciles->first()->subject_imageName, $location);
        $columns = $this->service->setColumnMasks($reconciles->first()->columns);

        return view('admin.reconcile.index', compact('reconciles', 'columns', 'imgUrl', 'expedition'));
    }

    /**
     * Set up data and redirect to index for processing.
     *
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(string $expeditionId): RedirectResponse
    {
        $expedition = $this->expeditionContract->findWith($expeditionId, ['project.group.owner']);

        if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
            return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expedition->id]);
        }

        NfnExpertReviewJob::dispatch($expedition->id);

        Flash::success(__('The job to create the Expert Review has been submitted. You will receive an email when it is complete and review can begin.'));

        return redirect()->route('admin.expeditions.show', [$expedition->project_id, $expeditionId]);
    }

    /**
     * Update reconciled record.
     *
     * @param string $expeditionId
     * @return array
     */
    public function update(string $expeditionId): array
    {
        if (! request()->ajax()) {
            return ['result' => false, 'message' => __('Error while updating record.')];
        }

        if (! $this->service->updateRecord(request()->all())) {
            return ['result' => false, 'message' => __('Error while updating record.')];
        }

        return ['result' => true, 'message' => __('Record was updated successfully.')];
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
        Flash::success(__('The Expert Review Publish job has been submitted. You will receive and email when it has completed.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }
}
