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

use App\Jobs\ZooniverseExportBuildZipJob;
use App\Models\ExportQueue;
use App\Services\Actor\QueueInterface;
use App\Services\Actor\Traits\ActorDirectory;
use App\Services\Api\AwsS3ApiService;
use App\Services\Csv\Csv;
use App\Repositories\ExportQueueFileRepository;
use App\Repositories\ExportQueueRepository;
use App\Services\Process\AwsS3CsvService;
use App\Services\Process\MapNfnCsvColumnsService;
use Exception;
use Illuminate\Support\Collection;

/**
 * Class ZooniverseBuildCsv
 */
class ZooniverseBuildCsv implements QueueInterface
{
    use ActorDirectory;

    /**
     * @var \App\Repositories\ExportQueueRepository
     */
    private ExportQueueRepository $exportQueueRepository;

    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * @var \App\Services\Api\AwsS3ApiService
     */
    private AwsS3ApiService $awsS3ApiService;

    /**
     * @var \App\Services\Csv\Csv
     */
    private Csv $csv;

    /**
     * @var \App\Services\Process\AwsS3CsvService
     */
    private AwsS3CsvService $awsS3CsvService;

    /**
     * @var \App\Services\Process\MapNfnCsvColumnsService
     */
    private MapNfnCsvColumnsService $mapNfnCsvColumnsService;

    /**
     * @var mixed|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application
     */
    private mixed $nfnCsvMap;

    /**
     * Construct.
     *
     * TODO check later to break this into other classes to reduce DI.
     *
     * @param \App\Repositories\ExportQueueRepository $exportQueueRepository
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     * @param \App\Services\Process\AwsS3CsvService $awsS3CsvService
     * @param \App\Services\Process\MapNfnCsvColumnsService $mapNfnCsvColumnsService
     */
    public function __construct(
        ExportQueueRepository $exportQueueRepository,
        ExportQueueFileRepository $exportQueueFileRepository,
        AwsS3CsvService $awsS3CsvService,
        MapNfnCsvColumnsService $mapNfnCsvColumnsService
    )
    {
        $this->exportQueueRepository = $exportQueueRepository;
        $this->exportQueueFileRepository = $exportQueueFileRepository;
        $this->awsS3CsvService = $awsS3CsvService;
        $this->mapNfnCsvColumnsService = $mapNfnCsvColumnsService;
    }

    /**
     * Process actor.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @return void
     * @throws \Exception
     */
    public function process(ExportQueue $exportQueue)
    {
        $exportQueue->load(['expedition']);

        $this->setFolder($exportQueue->id, $exportQueue->actor_id, $exportQueue->expedition->uuid);
        $this->setDirectories();

        $csvFilePath = $this->workingDir.'/'.$exportQueue->expedition->uuid.'.csv';
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $csvFilePath, 'w');
        $this->awsS3CsvService->createCsvWriterFromStream();
        $this->awsS3CsvService->addEncodingFormatter();

        $first = true;

        $this->exportQueueFileRepository->model()->chunk(100, function ($chunk) use (&$exportQueue, &$first) {
            $csvData = $chunk->filter(function ($file) {
                return $this->checkFileExists($this->workingDir.'/'.$file->subject_id.'.jpg', $file->subject_id);
            })->map(function ($file) use ($exportQueue) {
                return $this->mapNfnCsvColumnsService->mapColumns($file, $exportQueue);
            });

            if (empty($csvData)) {
                throw new Exception(t('CSV data empty while creating file for Expedition ID: %s', $exportQueue->expedition->id));
            }

            $this->buildCsv($csvData, $first);
            $first = false;

            $exportQueue->processed = $exportQueue->processed + $chunk->count();
            $exportQueue->save();
        });

        $this->awsS3CsvService->closeBucketStream();

        $this->updateRejected($this->rejected);

        if (! $this->checkCsvImageCount($exportQueue)) {
            throw new Exception(t('The row count in the csv export file does not match image count.'));
        }

        $exportQueue->stage = 5;
        $exportQueue->save();

        \Artisan::call('export:poll');

        //ZooniverseExportBuildZipJob::dispatch($exportQueue);
    }

    /**
     * Create csv file.
     *
     * @param \Illuminate\Support\Collection $data
     * @param bool $first
     * @throws \League\Csv\CannotInsertRecord
     */
    private function buildCsv(Collection $data, bool $first = false)
    {
        if ($first) {
            $this->awsS3CsvService->insertOne(array_keys($data->first()));
        }

        $this->awsS3CsvService->insertAll($data->toArray());
    }

    /**
     * Check csv row count to image count.
     * Do not set csv header offset. Since csv is in same dir as image, it will add 1 to the count.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @return bool
     */
    private function checkCsvImageCount(ExportQueue $exportQueue): bool
    {
        $csvFilePath = $this->workingDir.'/'.$exportQueue->expedition->uuid.'.csv';
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $csvFilePath, 'r');
        $this->awsS3CsvService->createCsvReaderFromStream();
        $csvCount = $this->awsS3CsvService->getReaderCount();

        $dirFileCount = $this->awsS3CsvService->getFileCount(config('filesystems.disks.s3.bucket'), $this->workingDir);

        $this->awsS3CsvService->closeBucketStream();

        return $csvCount === $dirFileCount;
    }

    /**
     * Update rejected files.
     *
     * @param array $rejected
     */
    public function updateRejected(array $rejected = [])
    {
        if (empty($rejected)) {
            return;
        }

        foreach ($rejected as $subjectId => $reason) {
            $file = $this->exportQueueFileRepository->findBy('subject_id', $subjectId);
            if (empty($file)) {
                $reason .= t(' Empty file result from DB for: %s', $subjectId);
            }

            $file->error_message .= ' ' . $reason;
            $file->save();
        }
    }
}