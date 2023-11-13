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

namespace App\Services\Actors\Zooniverse;

use App\Models\Download;
use App\Notifications\ZooniverseBatchExportComplete;
use App\Services\Actors\Traits\ActorBatchDirectory;
use App\Services\Csv\Csv;
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
    use ActorBatchDirectory;

    /**
     * @var array
     */
    private array $fileNames = [];

    /**
     * @var \App\Services\Csv\Csv
     */
    private Csv $csv;

    /**
     * Construct
     *
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(Csv $csv)
    {
        $this->csv = $csv;
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
        $this->setBatchFolderName($download->file);
        $this->setBatchDirectories();
        $this->copyExistingFile();
        $this->extractFile();

        $this->setCsvReader();
        $this->processCsvRows();

        exec("rm -r {$this->fullBatchWorkingDir}");

        Storage::disk('efs')->delete("{$this->efsBatchDir}/{$this->existingExportFile}");

        $links = $this->buildLinks();

        $this->owner->notify(new ZooniverseBatchExportComplete($this->expedition->title, $links));
    }

    /**
     * Copy existing export file to efs batch directory.
     *
     * @return void
     * @throws \Exception
     */
    private function copyExistingFile()
    {
        if ($this->checkS3ExportFileExists()) {

            exec("aws s3 cp {$this->existingBucketExportFile} {$this->fullEfsBatchDir}", $output, $retval);
            if ($retval !== 0) {
                throw new Exception("Could not copy {$this->existingBucketExportFile} to {$this->fullEfsBatchDir}/{$this->existingExportFile}");
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
    private function extractFile()
    {
        if ($this->checkEfsExportFileExists()) {

            $cmd = $this->setCommand();
            exec($cmd, $output, $retvalue);

            if ($retvalue !== 0) {
                throw new Exception("Could not decompress {$this->fullEfsBatchDir}/{$this->existingExportFile}");
            }

            return;
        }

        throw new Exception(t('The archive file does not exist.'));
    }

    /**
     * Set command based on extension type.
     *
     * @return string
     */
    private function setCommand(): string
    {
        return $this->fileExtension === '.zip' ?
            "unzip {$this->fullEfsBatchDir}/{$this->existingExportFile} -d {$this->fullBatchWorkingDir}" :
            "tar -xf {$this->fullEfsBatchDir}/{$this->existingExportFile} -C {$this->fullBatchWorkingDir}";

    }

    /**
     * Read Csv file into array chunks.
     *
     * @throws \League\Csv\Exception
     */
    private function setCsvReader()
    {
        $csvFilePath = $this->batchWorkingDir.'/'.$this->expedition->uuid.'.csv';
        $this->csv->readerCreateFromPath(Storage::disk('efs')->path($csvFilePath));
        $this->csv->setHeaderOffset();
    }

    /**
     * Process chunked csv array.
     *
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    private function processCsvRows()
    {
        $chunks = array_chunk(iterator_to_array($this->csv->getRecords(), true), 1000);

        foreach ($chunks as $batch => $chunk) {

            $this->fileNames[] = $fileName = $batch.'-'.$this->actor->id.'-'.$this->expedition->uuid;
            $this->setBatchTmpDirectory($fileName);

            foreach ($chunk as $row) {
                $this->moveFile($row['imageName']);
            }

            $this->createCsv($chunk, $fileName);

            $this->createZipFile($fileName);

            $this->uploadZipToS3($fileName);

            exec("rm -r {$this->fullBatchTmpDir}");
        }
    }

    /**
     * Move image file to tmp directory.
     *
     * @param string $fileName
     */
    private function moveFile(string $fileName)
    {
        $filePath = Storage::disk('efs')->path($this->batchWorkingDir.'/'.$fileName);
        $tmpPath = Storage::disk('efs')->path($this->batchTmpDir.'/'.$fileName);
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
        $csvFileName = $fileName.'.csv';
        $csvFilePath = $this->batchTmpDir.'/'.$csvFileName;

        $this->csv->writerCreateFromPath(Storage::disk('efs')->path($csvFilePath));
        $this->csv->insertOne(array_keys(reset($chunk)));
        $this->csv->insertAll($chunk);
    }

    /**
     * Create zip file for batch.
     *
     * @param string $fileName
     * @throws \Exception
     */
    private function createZipFile(string $fileName)
    {
        $batchExportZipFile = Storage::disk('efs')->path($this->batchWorkingDir.'/'.$fileName.'.zip');
        $batchTmpDirPath = Storage::disk('efs')->path($this->batchTmpDir);

        exec("zip -r -j {$batchExportZipFile} $batchTmpDirPath", $out, $ok);

        if ($ok === 0) {
            return;
        }

        throw new Exception('Could not create compressed export batch file for Expedition: '.$this->expedition->title);
    }

    /**
     * Upload batch zip to s3 bucket.
     *
     * @param string $fileName
     * @return void
     * @throws \Exception
     */
    private function uploadZipToS3(string $fileName)
    {
        $batchExportZipFile = Storage::disk('efs')->path($this->batchWorkingDir.'/'.$fileName.'.zip');

        exec("aws s3 mv $batchExportZipFile {$this->bucketBatchDir}/$fileName.zip", $output, $retval);

        if ($retval !== 0) {
            throw new Exception("Could not copy $batchExportZipFile to {$this->bucketBatchDir}");
        }
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
            $url = Storage::disk('s3')->temporaryUrl(config('config.batch_dir').'/'.$fileName . '.zip', now()->addHours(72), ['ResponseContentDisposition' => 'attachment']);

            $links[] = '<a href="'.$url.'">'.$fileName.'</a>';
        }

        return $links;
    }
}