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

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Jobs\ReconciledPublishJob;
use App\Services\Model\ReconcileService;
use Illuminate\Http\RedirectResponse;
use Session;

class ReconcilesController extends Controller
{
    /**
     * @var \App\Services\Model\ReconcileService
     */
    private $service;

    /**
     * ReconcilesController constructor.
     *
     * @param \App\Services\Model\ReconcileService $service
     */
    public function __construct(ReconcileService $service)
    {
        $this->service = $service;
    }

    /**
     * Show files needing reconciliation with pagination.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(string $projectId, string $expeditionId)
    {
        $expedition = $this->service->getExpeditionById($expeditionId);

        if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
            return redirect()->route('admin.expeditions.show', [$projectId, $expedition->id]);
        }

        if (! Session::has('reconcile')) {
            FlashHelper::error(trans('pages.missing_reconcile_data'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expedition->id]);
        }

        [$reconciles, $data] = $this->service->getPagination();

        if (! $reconciles) {
            FlashHelper::error(trans('pages.missing_reconcile_data'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expedition->id]);
        }

        $imgUrl = $this->service->getImageUrl($reconciles->first());

        return view('admin.reconcile.index', compact('reconciles', 'data', 'imgUrl', 'projectId', 'expeditionId'));
    }

    /**
     * Set up data and redirect to index for processing.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reconcile(string $projectId, string $expeditionId): RedirectResponse
    {
        $error = $this->service->migrateReconcileCsv($expeditionId);
        if ($error) {
            FlashHelper::error($error);

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        $this->service->setData();

        return redirect()->route('admin.reconciles.index', [$projectId, $expeditionId]);
    }

    /**
     * Update reconciled record.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return array
     */
    public function update(string $projectId, string $expeditionId): array
    {
        if (! request()->ajax()) {
            return ['result' => false, 'message' => __('pages.record_updated_error')];
        }

        if (! $this->service->updateRecord(request()->all())) {
            return ['result' => false, 'message' => __('pages.record_updated_error')];
        }

        return ['result' => true, 'message' => __('pages.record_updated')];
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
        ReconciledPublishJob::dispatch($expeditionId);
        FlashHelper::success(__('pages.reconciled_publish'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }
}
