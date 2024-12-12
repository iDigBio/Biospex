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

namespace App\Services\Actor\Zooniverse;

use App\Models\Download;
use App\Notifications\Generic;
use App\Services\Actor\ActorBatchDirectory;
use App\Services\Csv\Csv;
use Exception;
use File;
use Storage;

/**
 * Class ZooniverseExportBatch
 */
class ZooniverseExportBatch
{
    private array $fileNames = [];

    /**
     * Construct
     */
    public function __construct(protected Csv $csv, protected ActorBatchDirectory $actorBatchDirectory) {}

    /**
     * Process download into batches.
     *
     * @throws \Exception
     */
    public function process(Download $download): void
    {
        $this->actorBatchDirectory->setProperties($download);
        $this->actorBatchDirectory->setBatchFolderName($download->file);
        $this->actorBatchDirectory->setBatchDirectories();
        $this->copyExistingFile($this->actorBatchDirectory);
        $this->extractFile($this->actorBatchDirectory);

        $this->setCsvReader($this->actorBatchDirectory);
        $this->processCsvRows($this->actorBatchDirectory);

        exec("rm -r {$this->actorBatchDirectory->fullBatchWorkingDir}");

        Storage::disk('efs')->delete("{$this->actorBatchDirectory->efsBatchDir}/{$this->actorBatchDirectory->existingExportFile}");

        $links = $this->buildLinks();

        $attributes = [
            'subject' => t('Zooniverse Batch Export Completed'),
            'html' => [
                t('The export batches for %s are completed.', $download->expedition->title),
                t('The links provided below will be valid for 72 hours. Click the links to download each batch file. You must be logged into your account on Biospex.'),
                implode('<br>', $links),
            ],
        ];

        $download->expedition->project->group->owner->notify(new Generic($attributes));
    }

    /**
     * Copy existing export file to efs batch directory.
     *
     * @throws \Exception
     */
    private function copyExistingFile(ActorBatchDirectory $actorBatchDirectory): void
    {
        if ($actorBatchDirectory->checkS3ExportFileExists()) {

            exec("aws s3 cp {$actorBatchDirectory->existingBucketExportFile} {$actorBatchDirectory->fullEfsBatchDir}", $output, $retval);
            if ($retval !== 0) {
                throw new Exception("Could not copy {$actorBatchDirectory->existingBucketExportFile} to {$actorBatchDirectory->fullEfsBatchDir}/{$actorBatchDirectory->existingExportFile}");
            }

            return;
        }

        throw new Exception(t('The existing export file does not exist.'));
    }

    /**
     * Extract archive file to working directory.
     *
     * @throws \Exception
     */
    private function extractFile(ActorBatchDirectory $actorBatchDirectory): void
    {
        if ($actorBatchDirectory->checkEfsExportFileExists()) {

            $cmd = $this->setCommand($actorBatchDirectory);
            exec($cmd, $output, $retvalue);

            if ($retvalue !== 0) {
                throw new Exception("Could not decompress {$actorBatchDirectory->fullEfsBatchDir}/{$actorBatchDirectory->existingExportFile}");
            }

            return;
        }

        throw new Exception(t('The archive file does not exist.'));
    }

    /**
     * Set command based on extension type.
     */
    private function setCommand(ActorBatchDirectory $actorBatchDirectory): string
    {
        return $actorBatchDirectory->fileExtension === '.zip' ?
            "unzip {$actorBatchDirectory->fullEfsBatchDir}/{$actorBatchDirectory->existingExportFile} -d {$actorBatchDirectory->fullBatchWorkingDir}" :
            "tar -xf {$actorBatchDirectory->fullEfsBatchDir}/{$actorBatchDirectory->existingExportFile} -C {$actorBatchDirectory->fullBatchWorkingDir}";

    }

    /**
     * Read Csv file into array chunks.
     *
     * @throws \League\Csv\Exception
     */
    private function setCsvReader(ActorBatchDirectory $actorBatchDirectory): void
    {
        $csvFilePath = $actorBatchDirectory->batchWorkingDir.'/'.$actorBatchDirectory->expedition->uuid.'.csv';
        $this->csv->readerCreateFromPath(Storage::disk('efs')->path($csvFilePath));
        $this->csv->setHeaderOffset();
    }

    /**
     * Process chunked csv array.
     *
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    private function processCsvRows(ActorBatchDirectory $actorBatchDirectory): void
    {
        $chunks = array_chunk(iterator_to_array($this->csv->getRecords(), true), 1000);

        foreach ($chunks as $batch => $chunk) {

            $this->fileNames[] = $fileName = $batch.'-'.$actorBatchDirectory->actor->id.'-'.$actorBatchDirectory->expedition->uuid;
            $actorBatchDirectory->setBatchTmpDirectory($fileName);

            foreach ($chunk as $row) {
                $this->moveFile($row['imageName'], $actorBatchDirectory);
            }

            $this->createCsv($chunk, $fileName, $actorBatchDirectory);

            $this->createZipFile($fileName, $actorBatchDirectory);

            $this->uploadZipToS3($fileName, $actorBatchDirectory);

            exec("rm -r {$actorBatchDirectory->fullBatchTmpDir}");
        }
    }

    /**
     * Move image file to tmp directory.
     */
    private function moveFile(string $fileName, ActorBatchDirectory $actorBatchDirectory): void
    {
        $filePath = Storage::disk('efs')->path($actorBatchDirectory->batchWorkingDir.'/'.$fileName);
        $tmpPath = Storage::disk('efs')->path($actorBatchDirectory->batchTmpDir.'/'.$fileName);
        File::move($filePath, $tmpPath);
    }

    /**
     * Create csv file for batch.
     *
     * @throws \League\Csv\CannotInsertRecord
     */
    private function createCsv(array $chunk, string $fileName, ActorBatchDirectory $actorBatchDirectory): void
    {
        $csvFileName = $fileName.'.csv';
        $csvFilePath = $actorBatchDirectory->batchTmpDir.'/'.$csvFileName;

        $this->csv->writerCreateFromPath(Storage::disk('efs')->path($csvFilePath));
        $this->csv->insertOne(array_keys(reset($chunk)));
        $this->csv->insertAll($chunk);
    }

    /**
     * Create zip file for batch.
     *
     * @throws \Exception
     */
    private function createZipFile(string $fileName, ActorBatchDirectory $actorBatchDirectory): void
    {
        $batchExportZipFile = Storage::disk('efs')->path($actorBatchDirectory->batchWorkingDir.'/'.$fileName.'.zip');
        $batchTmpDirPath = Storage::disk('efs')->path($actorBatchDirectory->batchTmpDir);

        exec("zip -r -j {$batchExportZipFile} $batchTmpDirPath", $out, $ok);

        if ($ok === 0) {
            return;
        }

        throw new Exception('Could not create compressed export batch file for Expedition: '.$actorBatchDirectory->expedition->title);
    }

    /**
     * Upload batch zip to s3 bucket.
     *
     * @throws \Exception
     */
    private function uploadZipToS3(string $fileName, ActorBatchDirectory $actorBatchDirectory): void
    {
        $batchExportZipFile = Storage::disk('efs')->path($actorBatchDirectory->batchWorkingDir.'/'.$fileName.'.zip');

        exec("aws s3 mv $batchExportZipFile {$actorBatchDirectory->bucketBatchDir}/$fileName.zip", $output, $retval);

        if ($retval !== 0) {
            throw new Exception("Could not copy $batchExportZipFile to {$actorBatchDirectory->bucketBatchDir}");
        }
    }

    /**
     * Build links for download files.
     */
    private function buildLinks(): array
    {
        $links = [];
        foreach ($this->fileNames as $fileName) {
            $url = Storage::disk('s3')->temporaryUrl(config('config.batch_dir').'/'.$fileName.'.zip', now()->addHours(72), ['ResponseContentDisposition' => 'attachment']);

            $links[] = '<a href="'.$url.'">'.$fileName.'</a>';
        }

        return $links;
    }
}
