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
use App\Repositories\ExpeditionRepository;
use App\Repositories\UserRepository;
use App\Services\Download\DownloadType;
use Exception;
use Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class DownloadController
 *
 * @package App\Http\Controllers\Admin
 */
class DownloadController extends Controller
{
    /**
     * @var \App\Repositories\UserRepository
     */
    private UserRepository $userRepo;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepo;

    /**
     * @var \App\Services\Download\DownloadType
     */
    private DownloadType $downloadType;

    /**
     * DownloadController constructor.
     *
     * @param \App\Repositories\UserRepository $userRepo
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Services\Download\DownloadType $downloadType
     */
    public function __construct(
        UserRepository $userRepo,
        ExpeditionRepository $expeditionRepo,
        DownloadType $downloadType
    ) {
        $this->userRepo = $userRepo;
        $this->expeditionRepo = $expeditionRepo;
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
        $user = $this->userRepo->findWith(request()->user()->id, ['profile']);
        $expedition = $this->expeditionRepo->expeditionDownloadsByActor($expeditionId);

        $error = ! $this->checkPermissions('readProject', $expedition->project->group);

        return view('admin.expedition.partials.expedition-download-modal-body', compact('expedition', 'user', 'error'));
    }

    /**
     * Download report.
     *
     * @param string $fileName
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function report(string $fileName): StreamedResponse|RedirectResponse
    {
        try {
            return $this->downloadType->createReportDownload(base64_decode($fileName));
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
                Flash::error(t("The file record appears to be missing. If you'd like to have the file generated, please use the generate button in the Expedition tools."));

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
     * Download batch export file.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $fileName
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadZipBatch(string $projectId, string $expeditionId, string $fileName): StreamedResponse|RedirectResponse
    {
        try {
            $file = base64_decode($fileName).'.zip';
            $expedition = $this->expeditionRepo->findwith($expeditionId, ['project.group']);

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            return $this->downloadType->createBatchZipDownload($file);
        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Download batch export file.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $fileName
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadTarBatch(string $projectId, string $expeditionId, string $fileName)
    {
        try {
            $file = base64_decode($fileName).'.tar.gz';
            $expedition = $this->expeditionRepo->findwith($expeditionId, ['project.group']);

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            return $this->downloadType->createBatchTarDownload($file);
        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Generate export download.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export(string $projectId, string $expeditionId): RedirectResponse
    {
        try {
            \Artisan::call('export:queue', ['expeditionId' => $expeditionId]);

            Flash::success(t('Export generation has been added to the job queue. You will be notified when completed.'));
        } catch (Exception $e) {
            ;
            Flash::error(t('An error occurred while trying to generate the download. Please contact the administration with this error and the title of the Expedition.'));
        }

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Display summary.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function summary(string $projectId, string $expeditionId)
    {
        $expedition = $this->expeditionRepo->findwith($expeditionId, ['project.group']);

        if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        if (! Storage::exists(config('config.aws_s3_nfn_downloads.summary').'/'.$expeditionId.'.html')) {
            Flash::warning(t('File does not exist'));

            return redirect()->back();
        }

        return Storage::get(config('config.aws_s3_nfn_downloads.summary').'/'.$expeditionId.'.html');
    }

    /**
     * Send request to have export split into batch downloads.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $downloadId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batch(string $projectId, string $expeditionId, string $downloadId): RedirectResponse
    {
        ExportDownloadBatchJob::dispatch($downloadId);

        Flash::success(t('Your batch request has been submitted. You will receive an email with download links when the process is complete.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }
}
