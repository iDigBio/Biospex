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

use App\Models\Download;
use App\Notifications\NfnBatchExportComplete;
use Exception;
use File;
use function route;
use function t;

/**
 * Class ZooniverseExportBatch
 *
 * @package App\Services\Actor
 */
class ZooniverseExportBatch extends ZooniverseBase
{

    /**
     * @var array
     */
    private $fileNames = [];

    /**
     * Get Download.
     *
     * @param string $downloadId
     * @return \App\Models\Download
     */
    public function getDownload(string $downloadId): Download
    {
        return $this->dbService->downloadRepo->findWith($downloadId, ['expedition.project.group.owner', 'actor']);
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
        $this->setExpedition($download->expedition);
        $this->setActor($download->actor);
        $this->setOwner($download->expedition->project->group->owner);
        $this->actorDirectory->setBatchFolder($download->file);
        $this->actorDirectory->setDirectories(true, true);
    }

    /**
     * Extract archive file to working directory.
     *
     * @throws \Exception
     */
    private function extractFile()
    {
        if (File::isFile($this->archiveTarGzPath)) {
            exec('tar -xzf '.$this->archiveTarGzPath.' --directory '.$this->workingDirectory);

            return;
        }

        throw new Exception(t('The archive file does not exist.'));
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

            $this->fileNames[] = $fileName = $batch . '-' . $this->actor->id . '-' . $this->expedition->uuid;

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
        $tarFilePath = $this->actorDirectory->setBatchArchiveTarGz($fileName);

        exec("cd {$this->tmpDirectory} && find . \( -name '*.jpg' -o -name '*.csv' \) -print >../export.manifest");
        exec("cd {$this->tmpDirectory} && tar -czf $tarFilePath --files-from ../export.manifest", $out, $ok);

        if (! $ok) {
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
            $url = route('admin.downloads.downloadTarBatch', [
                'projects' => $this->expedition->project_id,
                'expeditions' => $this->expedition->id,
                'files' => base64_encode($fileName)
            ]);

            $links[] = '<a href="'.$url.'">' . $fileName . '</a>';
        }

        return $links;
    }
}