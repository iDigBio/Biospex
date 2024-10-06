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
use App\Models\Expedition;
use App\Services\Expedition\ExpeditionService;
use App\Services\Models\UserModelService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * Class DownloadController
 */
class DownloadController extends Controller
{
    /**
     * DownloadController constructor.
     */
    public function __construct(
        private UserModelService $userModelService,
        private ExpeditionService $expeditionService,
    ) {}

    /**
     * Index showing downloads for Expedition.
     */
    public function index(int $projectId, int $expeditionId): Factory|View
    {
        $user = $this->userModelService->findWithRelations(\Request::user()->id, ['profile']);
        $expedition = $this->expeditionService->expeditionDownloadsByActor($expeditionId);

        $error = ! $this->checkPermissions('readProject', $expedition->project->group);

        return \View::make('admin.expedition.partials.download-modal-body', compact('expedition', 'user', 'error'));
    }

    /**
     * Download report.
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
     */
    public function export(int $projectId, int $expeditionId): RedirectResponse
    {
        try {
            \Artisan::call('export:queue', ['expeditionId' => $expeditionId]);
            $status = 'success';
            $message = t('Export generation has been added to the job queue. You will be notified when completed.');
        } catch (Throwable $e) {
            $status = 'error';
            $message = t('An error occurred while trying to generate the download. Please contact the administration with this error and the title of the Expedition.');
        }

        return \Redirect::route('admin.expeditions.show', [$expeditionId])->with($status, $message);
    }

    /**
     * Send request to have export split into batch downloads.
     */
    public function batch(int $projectId, Expedition $expedition, string $downloadId): RedirectResponse
    {
        ExportDownloadBatchJob::dispatch($downloadId);

        return \Redirect::route('admin.expeditions.show', [$expedition])
            ->with('success', t('Your batch request has been submitted. You will receive an email with download links when the process is complete.'));
    }

    /**
     * Download geolocate file.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function geoLocate(string $file)
    {
        // TODO: This is a temporary solution to download the file. It should be refactored to use a proper download method.
        $fileString = explode('/', base64_decode($file));
        $url = Storage::disk('s3')->temporaryUrl(base64_decode($file), now()->addMinutes(5), ['ResponseContentDisposition' => 'attachment;filename=geolocate-export-'.$fileString[2]]);

        return redirect($url);
    }
}
