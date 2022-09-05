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

namespace App\Services\Download;

use App\Models\Download;
use App\Models\Expedition;
use App\Repositories\DownloadRepository;
use App\Services\Actor\ActorFactory;
use File;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class DownloadType
 *
 * @package App\Services\Download
 */
class DownloadType extends DownloadFileBase
{
    /**
     * @var \App\Repositories\DownloadRepository
     */
    private DownloadRepository $downloadRepo;

    /**
     * @var string
     */
    private $missingMsg;

    /**
     * DownloadType constructor.
     *
     * @param \App\Repositories\DownloadRepository $downloadRepo
     */
    public function __construct(
        DownloadRepository $downloadRepo
    ) {
        $this->downloadRepo = $downloadRepo;
        $this->missingMsg = t("The file appears to be missing though the records exist. Please contact the administration.");
    }

    /**
     * Created download for report.
     *
     * @param string $fileName
     * @return
     * @throws \Exception
     */
    public function createReportDownload(string $fileName)
    {
        $this->setFilePath(config('config.aws_s3_reports_dir'), $fileName);

        if (! $this->checkS3FileExists()) {
            throw new \Exception($this->missingMsg);
        }

        $this->setHeaderFileName($fileName);

        $headers = $this->setCsvHeaders();

        return Storage::disk('s3')->download($this->filePath, $fileName, $headers);
    }

    /**
     * Create html download file.
     *
     * @param \App\Models\Download $download
     * @return array
     * @throws \Exception
     */
    public function createHtmlDownload(Download $download): array
    {
        $this->setFilePath(config('config.aws_s3_nfn_downloads.summary'), $download->file);

        if (! $this->checkFileExists()) {
            throw new \Exception($this->missingMsg);
        }

        $this->setHeaderFileName($download->present()->file_type.'-'.$download->file);
        $this->setStoragePath();
        $headers = $this->setHtmlHeaders();
        $filePath = $this->getStoragePath();

        return [$filePath, $headers];
    }

    /**
     * Create csv download file.
     *
     * @param \App\Models\Download $download
     * @return array
     * @throws \Exception
     */
    public function createCsvDownload(Download $download): array
    {
        $this->setFilePath(config('config.nfn_downloads_'.$download->type), $download->file);

        if (! $this->checkFileExists()) {
            throw new \Exception($this->missingMsg);
        }

        $this->setHeaderFileName($download->present()->file_type.'-'.$download->file);
        $this->setStoragePath();

        $headers = $this->setCsvHeaders();
        $reader = $this->getReader();

        return [$reader, $headers];
    }

    /**
     * Create tar download file.
     *
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Exception
     */
    public function createBatchZipDownload(string $fileName): StreamedResponse
    {
        $this->setFilePath(config('config.aws_s3_export_dir'), $fileName);

        if (! $this->checkS3FileExists()) {
            throw new \Exception($this->missingMsg);
        }

        $this->setHeaderFileName($fileName);
        $headers = $this->setZipHeaders();

        return Storage::disk('s3')->download($this->filePath, $this->headerFileName, $headers);
    }

    /**
     * Create tar download file.
     *
     * @param string $fileName
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Exception
     */
    public function createBatchTarDownload(string $fileName)
    {
        $this->setFilePath(config('config.aws_s3_export_dir'), $fileName);

        if (! $this->checkS3FileExists()) {
            throw new \Exception($this->missingMsg);
        }

        $this->setHeaderFileName($fileName);
        $headers = $this->setTarHeaders();

        return Storage::disk('s3')->download($this->filePath, $this->headerFileName, $headers);
    }

    /**
     * Get download.
     *
     * @param string $downloadId
     * @return \App\Models\Download
     */
    public function getDownload(string $downloadId): Download
    {
        return $this->downloadRepo->findWith($downloadId, ['expedition.project.group.owner', 'actor']);
    }

    /**
     * Delete existing exports files for expedition.
     *
     * @param string $expeditionId
     */
    public function deleteExportFiles(string $expeditionId)
    {
        $downloads = $this->downloadRepo->getExportFiles($expeditionId);

        $downloads->each(function ($download) {
            if (Storage::disk('s3')->exists(config('config.aws_s3_export_dir').'/'.$download->file)) {
                Storage::disk('s3')->delete(config('config.aws_s3_export_dir').'/'.$download->file);
            }
            $download->delete();
        });
    }

    /**
     * Reset data for expedition when regenerating export.
     *
     * @param \App\Models\Expedition $expedition
     */
    public function resetExpeditionData(Expedition $expedition)
    {
        $this->deleteExportFiles($expedition->id);

        $attributes = [
            'state' => 0,
            'total' => $expedition->stat->local_subject_count,
        ];

        $expedition->nfnActor->expeditions()->updateExistingPivot($expedition->id, $attributes);

        ActorFactory::create($expedition->nfnActor->class)->actor($expedition->nfnActor);
    }
}
