<?php

namespace App\Services\Actor;

use App\Models\Download;
use App\Notifications\NfnBatchExportComplete;
use App\Services\Csv\Csv;
use App\Services\Model\DownloadService;
use File;

class NfnPanoptesExportBatch extends NfnPanoptesBase
{
    /**
     * @var \App\Services\Model\DownloadService
     */
    private $downloadService;

    /**
     * @var \App\Services\Csv\Csv
     */
    private $csv;

    /**
     * @var array
     */
    private $fileNames = [];

    /**
     * DownloadBatchService constructor.
     *
     * @param \App\Services\Model\DownloadService $downloadService
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        DownloadService $downloadService,
        Csv $csv
    )
    {
        $this->downloadService = $downloadService;
        $this->csv = $csv;
    }

    /**
     * Get download.
     *
     * @param string $downloadId
     * @return \App\Models\Download
     */
    public function getDownload(string $downloadId): Download
    {
        return $this->downloadService->getDownload($downloadId);
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
        $chunks = $this->readCsv();
        $this->processChunks($chunks);

        File::deleteDirectory($this->workingDirectory);

        $message = __('html.export_batch_message', ['expedition' => $this->expedition->title]);
        $links = $this->buildLinks();

        $this->owner->notify(new NfnBatchExportComplete($message, $links));

        return;
    }

    /**
     * Set properties.
     *
     * @param \App\Models\Download $download
     * @throws \Exception
     */
    private function setProperties(Download $download)
    {
        $this->setExpedition($download->expedition);
        $this->setActor($download->actor);
        $this->setOwner($download->expedition->project->group->owner);
        $this->setFolder();
        $this->setDirectories(true);
    }

    /**
     * Extract archive file to working directory.
     *
     * @throws \Exception
     */
    private function extractFile()
    {
        if (File::isFile($this->archiveExportPath)) {
            exec('tar -xzf '.$this->archiveExportPath.' --directory '.$this->workingDirectory);

            return;
        }

        throw new \Exception(__('messages.export_file_exist_error'));
    }

    /**
     * Read Csv file into array chunks.
     *
     * @return array
     * @throws \League\Csv\Exception
     */
    private function readCsv(): array
    {
        $csv = $this->workingDirectory . '/' . $this->expedition->uuid . '.csv';
        $this->csv->readerCreateFromPath($csv);
        $this->csv->setDelimiter();
        $this->csv->setEnclosure();
        $this->csv->setHeaderOffset();

        return array_chunk(iterator_to_array($this->csv->getRecords(), true), 1000);
    }

    /**
     * Process chunked csv array.
     *
     * @param array $chunks
     * @throws \League\Csv\CannotInsertRecord|\Exception
     */
    private function processChunks(array $chunks)
    {
        foreach ($chunks as $batch => $chunk) {
            foreach($chunk as $row) {
                $this->moveFile($row['imageName']);
            }

            $this->fileNames[] = $fileName =$batch . '-' . $this->actor->id . '-' . $this->expedition->uuid;

            $this->createCsv($chunk, $fileName);

            $this->tarGzFile($fileName);

            File::deleteDirectory($this->tmpDirectory, true);
        }
    }

    /**
     * Move image file to tmp directory.
     *
     * @param string $fileName
     */
    private function moveFile(string $fileName)
    {
        $filePath = $this->workingDirectory . '/' . $fileName;
        $tmpPath = $this->tmpDirectory . '/' . $fileName;
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
        $csvFilePath = $this->tmpDirectory.'/'.$csvFileName;
        $this->csv->writerCreateFromPath($csvFilePath);
        $this->csv->insertOne(array_keys(reset($chunk)));
        $this->csv->insertAll($chunk);
    }

    /**
     * Create tar gx file for batch.
     *
     * @param string $fileName
     * @throws \Exception
     */
    private function tarGzFile(string $fileName)
    {
        $tarFilePath = $this->setBatchArchiveTarGz($fileName);

        exec("cd {$this->tmpDirectory} && find . \( -name '*.jpg' -o -name '*.csv' \) -print >../export.manifest");
        exec("cd {$this->tmpDirectory} && tar -czf $tarFilePath --files-from ../export.manifest", $out, $ok);

        if (! $ok) {
            return;
        }

        throw new \Exception('Could not create compressed export batch file for Expedition: ' . $this->expedition->title);
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
            $url = route('admin.downloads.batchDownload', [
                'projects' => $this->expedition->project_id,
                'expeditions' => $this->expedition->id,
                'files' => $fileName
            ]);

            $links[] = '<a href="'.$url.'">' . $fileName . '</a>';
        }

        return $links;
    }
}