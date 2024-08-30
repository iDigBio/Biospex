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
use App\Jobs\ExportDownloadBatchJob;
use App\Repositories\UserRepository;
use App\Services\Models\ExpeditionModelService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Class DownloadController
 *
 * @package App\Http\Controllers\Admin
 */
class DownloadController extends Controller
{
    /**
     * DownloadController constructor.
     *
     */
    public function __construct(
        private UserRepository $userRepo,
        private ExpeditionModelService $expeditionModelService,
    ) {}

    /**
     * Index showing downloads for Expedition.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(int $projectId, int $expeditionId): Factory|View
    {
        $user = $this->userRepo->findWith(\Request::user()->id, ['profile']);
        $expedition = $this->expeditionModelService->expeditionDownloadsByActor($expeditionId);

        $error = ! $this->checkPermissions('readProject', $expedition->project->group);

        return \View::make('admin.expedition.partials.download-modal-body', compact('expedition', 'user', 'error'));
    }

    /**
     * Download report.
     *
     * @param string $fileName
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function report(string $fileName): Redirector|RedirectResponse|Application
    {
        $url = Storage::disk('s3')->temporaryUrl(config('config.report_dir').'/'.base64_decode($fileName), now()->addMinutes(5), ['ResponseContentDisposition' => 'attachment;']);

        return redirect($url);
    }

    /**
     * Generate export download.
     *
     * @see \App\Console\Commands\ExportQueueCommand
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export(int $projectId, int $expeditionId): RedirectResponse
    {
        try {
            \Artisan::call('export:queue', ['expeditionId' => $expeditionId]);

            \Flash::success(t('Export generation has been added to the job queue. You will be notified when completed.'));
        } catch (Exception $e) {
            \Flash::error(t('An error occurred while trying to generate the download. Please contact the administration with this error and the title of the Expedition.'));
        }

        return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Send request to have export split into batch downloads.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @param string $downloadId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batch(int $projectId, int $expeditionId, string $downloadId): RedirectResponse
    {
        ExportDownloadBatchJob::dispatch($downloadId);

        \Flash::success(t('Your batch request has been submitted. You will receive an email with download links when the process is complete.'));

        return \Redirect::route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Download geolocate file.
     *
     * @param string $file
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function geoLocate(string $file)
    {
        $url = Storage::disk('s3')->temporaryUrl(base64_decode($file), now()->addMinutes(5), ['ResponseContentDisposition' => 'attachment;']);

        return redirect($url);
    }
}
