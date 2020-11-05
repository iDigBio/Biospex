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

use App\Jobs\ExportDownloadBatchJob;
use App\Services\Model\ExpeditionService;
use App\Services\Model\UserService;
use App\Services\Download\DownloadType;
use Exception;
use Flash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

/**
 * Class DownloadsController
 *
 * @package App\Http\Controllers\Admin
 */
class DownloadsController extends Controller
{
    /**
     * @var \App\Services\Model\UserService
     */
    private $userService;

    /**
     * @var \App\Services\Model\ExpeditionService
     */
    private $expeditionService;

    /**
     * @var \App\Services\Download\DownloadType
     */
    private $downloadType;

    /**
     * DownloadsController constructor.
     *
     * @param \App\Services\Model\UserService $userService
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @param \App\Services\Download\DownloadType $downloadType
     */
    public function __construct(
        UserService $userService,
        ExpeditionService $expeditionService,
        DownloadType $downloadType
    ) {
        $this->userService = $userService;
        $this->expeditionService = $expeditionService;
        $this->downloadType = $downloadType;
    }

    /**
     * Index showing downloads for Expedition.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(string $projectId, string $expeditionId)
    {
        $user = $this->userService->findWith(request()->user()->id, ['profile']);
        $expedition = $this->expeditionService->expeditionDownloadsByActor($expeditionId);

        $error = ! $this->checkPermissions('readProject', $expedition->project->group);

        return view('admin.partials.expedition-download-modal-body', compact('expedition', 'user', 'error'));
    }

    /**
     * Download report.
     *
     * @param string $fileName
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function report(string $fileName)
    {
        try {
            [$reader, $headers] = $this->downloadType->createReportDownload(base64_decode($fileName));

            return response($reader->getContent(), 200, $headers);
        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->route('admin.projects.index');
        }
    }

    /**
     * Create downloads for csv and html.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $downloadId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(string $projectId, string $expeditionId, string $downloadId)
    {
        try {

            $download = $this->downloadType->getDownload($downloadId);

            if (! $download) {
                Flash::error(t("The file record appears to be missing. If you'd like to have the file regenerated, please contact the Biospex Administrator using the contact form and specify the Expedition title."));

                return redirect()->back();
            }

            if ($download->type === 'summary') {

                [$filePath, $headers] = $this->downloadType->createHtmlDownload($download);

                return response()->download($filePath, null, $headers);
            }

            [$reader, $headers] = $this->downloadType->createCsvDownload($download);

            return response($reader->getContent(), 200, $headers);
        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Create download for tar files.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $downloadId
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTar(string $projectId, string $expeditionId, string $downloadId)
    {
        try {
            $download = $this->downloadType->getDownload($downloadId);

            if (! $this->checkPermissions('isOwner', $download->expedition->project->group)) {
                return redirect()->back();
            }

            [$filePath, $headers] = $this->downloadType->createTarDownload($download);

            return response()->download($filePath, null, $headers);
        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Download batch export file.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $fileName
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadTarBatch(string $projectId, string $expeditionId, string $fileName)
    {
        try {
            $file = base64_decode($fileName).'.tar.gz';
            $expedition = $this->expeditionService->findwith($expeditionId, ['project.group']);

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            [$filePath, $headers] = $this->downloadType->createBatchTarDownload($file);

            return response()->download($filePath, $file, $headers);
        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Regenerate export download.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerate(string $projectId, string $expeditionId)
    {
        try {
            $expedition = $this->expeditionService->findwith($expeditionId, ['nfnActor', 'stat']);

            $this->downloadType->resetExpeditionData($expedition);

            Flash::success(t('Download regeneration has started. You will be notified when completed.'));
        } catch (Exception $e) {
            Flash::error(t('An error occurred while trying to regenerate the download. The Admin has been notified.'));
        }

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Display summary.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse|string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function summary(string $projectId, string $expeditionId)
    {
        $expedition = $this->expeditionService->findwith($expeditionId, ['project.group']);

        if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        if (! Storage::exists(config('config.nfn_downloads_summary').'/'.$expeditionId.'.html')) {
            Flash::warning(t('File does not exist'));

            return redirect()->back();
        }

        return Storage::get(config('config.nfn_downloads_summary').'/'.$expeditionId.'.html');
    }

    /**
     * Send request to have export split into batch downloads.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $downloadId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batch(string $projectId, string $expeditionId, string $downloadId)
    {
        ExportDownloadBatchJob::dispatch($downloadId);

        Flash::success(t('Your batch request has been submitted. You will receive an email with download links when the process is complete.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }
}
