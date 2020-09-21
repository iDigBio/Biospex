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

use App\Jobs\ExportDownloadBatchJob;
use App\Services\Model\DownloadService;
use Exception;
use Flash;
use App\Http\Controllers\Controller;
use GeneralHelper;
use Illuminate\Support\Facades\Storage;
use League\Csv\Reader;

class DownloadsController extends Controller
{
    /**
     * @var \App\Services\Model\DownloadService
     */
    private $downloadService;

    /**
     * DownloadsController constructor.
     *
     * @param \App\Services\Model\DownloadService $downloadService
     */
    public function __construct(DownloadService $downloadService)
    {
        $this->downloadService = $downloadService;
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
        $user = $this->downloadService->getUserProfile(request()->user()->id);
        $expedition = $this->downloadService->getExpeditionByActor($projectId, $expeditionId);

        $error = ! $this->checkPermissions('readProject', $expedition->project->group);

        return view('admin.partials.expedition-download-modal-body', compact('expedition', 'user', 'error'));
    }

    /**
     * Show downloads.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $downloadId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Throwable
     */
    public function download(string $projectId, string $expeditionId, string $downloadId)
    {
        try {

            $download = $this->downloadService->getDownload($downloadId);

            if (! $download) {
                Flash::error(t("The file appears to be missing. If you'd like to have the file regenerated, please contact the Biospex Administrator using the contact form and specify the Expedition title."));

                return redirect()->back();
            }

            if ($download->type !== 'export' && ! $this->checkPermissions('isOwner', $download->expedition->project->group)) {
                return redirect()->back();
            }

            $this->downloadService->updateDownloadCount($download);

            if (! empty($download->data)) {
                [$view, $headers] = $this->downloadService->createDataView($download);

                return response()->make(stripslashes($view), 200, $headers);
            }

            if (! GeneralHelper::downloadFileExists($download->type, $download->file)) {
                Flash::error(t("The file appears to be missing. If you'd like to have the file regenerated, please contact the Biospex Administrator using the contact form and specify the Expedition title."));

                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            [$headers, $file] = $this->downloadService->createDownloadFile($download);

            return response()->download($file, $download->present()->file_type.'-'.$download->file, $headers);

        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->back();
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
            $this->downloadService->resetExpeditionData($expeditionId);

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
        $expedition = $this->downloadService->getExpeditionById($expeditionId, ['project.group']);

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
     * Download report.
     *
     * @param string $fileName
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function report(string $fileName)
    {
        try {

            $filePath = Storage::path(config('config.reports_dir') . '/' . $fileName);
            $reader = Reader::createFromPath($filePath, 'r');
            $reader->setOutputBOM(Reader::BOM_UTF8);

            $headers = [
                'Content-Encoding' => 'UTF-8',
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
                'Content-Description' => 'File Transfer',
            ];

            return response()->download($reader->output($fileName), $fileName, $headers);

        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->route('admin.projects.index');
        }
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

    /**
     * Download batch export file.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @param string $fileName
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function batchDownload(string $projectId, string $expeditionId, string $fileName)
    {
        $file = $fileName . '.tar.gz';

        try {
            $expedition = $this->downloadService->getExpeditionById($expeditionId, ['project.group']);

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            if (! GeneralHelper::downloadFileExists('export', $file)) {
                Flash::error(t("The file appears to be missing. If you'd like to have the file regenerated, please contact the Biospex Administrator using the contact form and specify the Expedition title."));

                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            [$headers, $filePath] = $this->downloadService->createBatchDownloadFile($file);

            return response()->download($filePath, $file, $headers);

        } catch (Exception $e) {
            Flash::error($e->getMessage());

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }
}
