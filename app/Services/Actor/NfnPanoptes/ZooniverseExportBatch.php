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

namespace App\Services\Actor\NfnPanoptes;

use App\Models\Actor;
use App\Models\Download;
use App\Models\Expedition;
use App\Models\User;
use App\Notifications\NfnBatchExportComplete;
use App\Services\Actor\Traits\ActorDirectory;
use App\Services\Process\AwsS3CsvService;
use Exception;
use File;
use Storage;

/**
 * Class ZooniverseExportBatch
 *
 * @package App\Services\Actor
 */
class ZooniverseExportBatch
{

    use ActorDirectory;

    /**
     * @var \App\Models\Actor
     */
    private Actor $actor;

    /**
     * @var \App\Models\Expedition
     */
    private Expedition $expedition;

    /**
     * @var \App\Models\User
     */
    private User $owner;

    /**
     * @var array
     */
    private array $fileNames = [];

    /**
     * @var \App\Services\Process\AwsS3CsvService
     */
    private AwsS3CsvService $awsS3CsvService;

    /**
     * Construct
     */
    public function __construct(AwsS3CsvService $awsS3CsvService)
    {
        $this->awsS3CsvService = $awsS3CsvService;
    }

    /**
     * Process download into batches.
     *
     * @param \App\Models\Download $download
     * @throws \Exception
     */
    public function process(Download $download)
    {
        $this->setProperties($download);

        $this->extractFile();

        $this->setCsvReader();

        $this->processCsvRows();
        $this->awsS3CsvService->closeBucketStream();

        Storage::disk('efs')->delete($this->batchWorkingDir);
        Storage::disk('efs')->delete($this->efsBatchDir . '/' . $this->exportArchiveFile);

        $links = $this->buildLinks();

        $this->owner->notify(new NfnBatchExportComplete($this->expedition->title, $links));
    }

    /**
     * Set properties.
     *
     * @param \App\Models\Download $download
     * @throws \Exception
     */
    private function setProperties(Download $download)
    {
        $this->expedition = $download->expedition;
        $this->actor = $download->actor;
        $this->owner = $download->expedition->project->group->owner;

        $this->setBatchFolderName($download->file);

        $this->setBatchDirectories();
    }

    /**
     * Extract archive file to working directory.
     *
     * @throws \Exception
     */
    private function extractFile()
    {
        if (Storage::disk('s3')->exists($this->exportArchiveFilePath)) {
            $archivePath = config('filesystems.disks.s3.bucket') . '/' . $this->exportArchiveFilePath;
            $efsArchivePath = $this->efsBatchDir . '/' . $this->exportArchiveFile;

            \Log::alert("aws s3 cp s3://$archivePath $efsArchivePath");
            exec("aws s3 cp s3://$archivePath $efsArchivePath", $output, $retval);
            if ($retval !== 0) {
                throw new Exception("Could not copy $archivePath to $efsArchivePath");
            }

            \Log::alert("unzip $efsArchivePath -d {$this->batchWorkingDir}");
            exec("unzip $efsArchivePath -d {$this->batchWorkingDir}", $output, $retvalue);
            if ($retvalue !== 0) {
                throw new Exception("Could not unzip $efsArchivePath");
            }

            return;
        }

        throw new Exception(t('The archive file does not exist.'));
    }

    /**
     * Read Csv file into array chunks.
     *
     * @throws \League\Csv\Exception
     */
    private function setCsvReader()
    {
        $csvFilePath = $this->batchWorkingDir . '/' . $this->expedition->uuid . '.csv';
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $csvFilePath, 'r');
        $this->awsS3CsvService->createCsvReaderFromStream();
        $this->awsS3CsvService->setHeaderOffset();
    }

    /**
     * Process chunked csv array.
     *
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    private function processCsvRows()
    {
        $chunks = array_chunk(iterator_to_array($this->awsS3CsvService->getRecords(), true), 1000);

        foreach ($chunks as $batch => $chunk) {

            $this->fileNames[] = $fileName = $batch . '-' . $this->actor->id . '-' . $this->expedition->uuid;
            $this->setBatchTmpDirectory($fileName);

            foreach($chunk as $row) {
                $this->moveFile($row['imageName']);
            }

            $this->createCsv($chunk, $fileName);

            $this->createZipFile($fileName);

            File::deleteDirectory($this->batchTmpDir);
        }
    }

    /**
     * Move image file to tmp directory.
     *
     * @param string $fileName
     */
    private function moveFile(string $fileName)
    {
        $filePath = $this->batchWorkingDir . '/' . $fileName;
        $tmpPath = $this->batchTmpDir . '/' . $fileName;
        File::move($filePath, $tmpPath);
    }

    /**
     * Create csv file for batch.
     *
     * @param array $chunk
     * @param string $fileName
     * @throws \League\Csv\CannotInsertRecord
     */
    private function createCsv(array $chunk, string $fileName)
    {
        $csvFileName = $fileName . '.csv';
        $csvFilePath = $this->batchTmpDir.'/'.$csvFileName;

        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $csvFilePath, 'w');
        $this->awsS3CsvService->createCsvWriterFromStream();
        $this->awsS3CsvService->insertOne(array_keys(reset($chunk)));
        $this->awsS3CsvService->insertAll($chunk);
        $this->awsS3CsvService->closeBucketStream();
    }

    /**
     * Create zip file for batch.
     *
     * @param string $fileName
     * @throws \Exception
     */
    private function createZipFile(string $fileName)
    {
        $batchExportZipFile = $this->setBatchExportZipFile($fileName);

        exec("zip {$batchExportZipFile} {$this->batchTmpDir}", $out, $ok);

        if ($ok === 0) {
            return;
        }

        throw new Exception('Could not create compressed export batch file for Expedition: ' . $this->expedition->title);
    }

    /**
     * Build links for download files.
     *
     * @return array
     */
    private function buildLinks(): array
    {
        $links = [];
        foreach ($this->fileNames as $fileName) {
            $url = Storage::disk('s3')->temporaryUrl(config('config.export_dir').'/'.$fileName, now()->addHours(48), ['ResponseContentDisposition' => 'attachment']);

            $links[] = '<a href="'.$url.'">' . $fileName . '</a>';
        }

        return $links;
    }
}