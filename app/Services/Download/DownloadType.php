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
use App\Models\User;
use App\Services\Actor\ActorFactory;
use App\Services\Model\DownloadService;
use File;
use Storage;

/**
 * Class DownloadType
 *
 * @package App\Services\Download
 */
class DownloadType extends DownloadFileBase
{
    /**
     * @var \App\Services\Model\DownloadService
     */
    private $downloadService;

    /**
     * @var string
     */
    private $missingMsg;

    /**
     * DownloadType constructor.
     *
     * @param \App\Services\Model\DownloadService $downloadService
     */
    public function __construct(
        DownloadService $downloadService
    ) {
        $this->downloadService = $downloadService;
        $this->missingMsg = t("The file appears to be missing though the records exist. Please contact the administration.");
    }

    /**
     * Created download for report.
     *
     * @param string $fileName
     * @return array
     * @throws \Exception
     */
    public function createReportDownload(string $fileName)
    {
        $this->setFilePath(config('config.reports_dir'), $fileName);

        if (! $this->checkFileExists()) {
            throw new \Exception($this->missingMsg);
        }

        $this->setHeaderFileName($fileName);
        $this->setStoragePath();

        $headers = $this->setCsvHeaders();
        $reader = $this->getReader();

        return [$reader, $headers];
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
        $this->setFilePath(config('config.nfn_downloads_summary'), $download->file);

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
     * @param \App\Models\Download $download
     * @return array
     * @throws \Exception
     */
    public function createTarDownload(Download $download)
    {
        $this->setFilePath(config('config.export_dir'), $download->file);

        if (! $this->checkFileExists()) {
            throw new \Exception($this->missingMsg);
        }

        $this->setHeaderFileName($download->present()->file_type.'-'.$download->file);
        $this->setStoragePath();

        $filePath = $this->getStoragePath();
        $headers = $this->setTarHeaders();

        return [$filePath, $headers];
    }

    /**
     * Create tar download file.
     *
     * @param string $fileName
     * @return array
     * @throws \Exception
     */
    public function createBatchTarDownload(string $fileName)
    {
        $this->setFilePath(config('config.export_dir'), $fileName);

        if (! $this->checkFileExists()) {
            throw new \Exception($this->missingMsg);
        }

        $this->setHeaderFileName($fileName);
        $this->setStoragePath();

        $filePath = $this->getStoragePath();
        $headers = $this->setTarHeaders();

        return [$filePath, $headers];
    }

    /**
     * Get download.
     *
     * @param string $downloadId
     * @return \App\Models\Download
     */
    public function getDownload(string $downloadId): Download
    {
        return $this->downloadService->findWith($downloadId, ['expedition.project.group.owner', 'actor']);
    }

    /**
     * Delete existing exports files for expedition.
     *
     * @param string $expeditionId
     */
    public function deleteExportFiles(string $expeditionId)
    {
        $downloads = $this->downloadService->getExportFiles($expeditionId);
        $nfnExportDir = Storage::path(config('config.export_dir'));

        $downloads->each(function ($download) use ($nfnExportDir) {
            $file = $nfnExportDir.'/'.$download->file;
            if (File::isFile($file)) {
                File::delete($file);
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

        $expedition->nfnActor->pivot->state = 0;
        $expedition->nfnActor->pivot->total = $expedition->stat->local_subject_count;

        event('actor.pivot.export', [$expedition->nfnActor]);

        ActorFactory::create($expedition->nfnActor->class)->actor($expedition->nfnActor);
    }
}
