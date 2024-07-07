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

use App\Jobs\ZooniverseExportBuildZipJob;
use App\Models\ExportQueue;
use App\Repositories\ExportQueueFileRepository;
use App\Services\Actor\ActorDirectory;
use App\Services\Csv\AwsS3CsvService;
use App\Services\Process\MapZooniverseCsvColumnsService;
use Exception;
use Illuminate\Support\Collection;

/**
 * Class ZooniverseBuildCsv
 */
class ZooniverseBuildCsv
{
    /**
     * @var \App\Repositories\ExportQueueFileRepository
     */
    private ExportQueueFileRepository $exportQueueFileRepository;

    /**
     * @var \App\Services\Csv\AwsS3CsvService
     */
    private AwsS3CsvService $awsS3CsvService;

    /**
     * @var \App\Services\Process\MapZooniverseCsvColumnsService
     */
    private MapZooniverseCsvColumnsService $mapZooniverseCsvColumnsService;

    /**
     * Construct.
     *
     * @param \App\Repositories\ExportQueueFileRepository $exportQueueFileRepository
     * @param \App\Services\Csv\AwsS3CsvService $awsS3CsvService
     * @param \App\Services\Process\MapZooniverseCsvColumnsService $mapZooniverseCsvColumnsService
     */
    public function __construct(
        ExportQueueFileRepository $exportQueueFileRepository,
        AwsS3CsvService $awsS3CsvService,
        MapZooniverseCsvColumnsService $mapZooniverseCsvColumnsService
    ) {
        $this->exportQueueFileRepository = $exportQueueFileRepository;
        $this->awsS3CsvService = $awsS3CsvService;
        $this->mapZooniverseCsvColumnsService = $mapZooniverseCsvColumnsService;
    }

    /**
     * Process actor.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @param \App\Services\Actor\ActorDirectory $actorDirectory
     * @return void
     * @throws \League\Csv\CannotInsertRecord
     * @throws \Exception
     */
    public function process(ExportQueue $exportQueue, ActorDirectory $actorDirectory): void
    {
        $exportQueue->load(['expedition']);

        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $actorDirectory->exportCsvFilePath, 'w');
        $this->awsS3CsvService->createCsvWriterFromStream();
        $this->awsS3CsvService->csv->addEncodingFormatter();

        $first = true;
        $this->exportQueueFileRepository->model()->chunk(config('config.aws.lambda_export_count'), function ($chunk) use
        (
            $exportQueue,
            $actorDirectory,
            &$first
        ) {
            $csvData = $chunk->filter(function ($file) use ($actorDirectory) {
                \Log::info('Checking file: '.$file->subject_id.'.jpg');
                return $actorDirectory->checkS3FileExists($actorDirectory->workingDir.'/'.$file->subject_id.'.jpg');
            })->map(function ($file) use ($exportQueue) {
                \Log::info('Mapping file: '.$file->subject_id.'.jpg');
                return $this->mapZooniverseCsvColumnsService->mapColumns($file, $exportQueue);
            });

            if (empty($csvData)) {
                throw new Exception(t('CSV data empty while creating file for Expedition ID: %s', $exportQueue->expedition->id));
            }

            $this->buildCsv($csvData, $first);
            $first = false;
        });

        if (! $this->checkCsvImageCount($actorDirectory)) {
            throw new Exception(t('The row count in the csv export file does not match image count.'));
        }

        ZooniverseExportBuildZipJob::dispatch($exportQueue, $actorDirectory);
    }

    /**
     * Create csv file.
     *=
     *
     * @param \Illuminate\Support\Collection $data
     * @param bool $first
     * @throws \League\Csv\CannotInsertRecord
     */
    private function buildCsv(Collection $data, bool $first = false): void
    {
        if ($first) {
            $this->awsS3CsvService->csv->insertOne(array_keys($data->first()));
        }

        $this->awsS3CsvService->csv->insertAll($data->toArray());
    }

    /**
     * Check csv row count to image count.
     * Do not set csv header offset. Since csv is in same dir as image, it will add 1 to the count.
     *
     * @param \App\Services\Actor\ActorDirectory $actorDirectory
     * @return bool
     */
    private function checkCsvImageCount(ActorDirectory $actorDirectory): bool
    {
        $this->awsS3CsvService->createBucketStream(config('filesystems.disks.s3.bucket'), $actorDirectory->exportCsvFilePath, 'r');
        $this->awsS3CsvService->createCsvReaderFromStream();
        $csvCount = $this->awsS3CsvService->csv->getReaderCount();

        $dirFileCount = $this->awsS3CsvService->getCsvRowCount($actorDirectory->workingDir);

        return $csvCount === $dirFileCount;
    }
}