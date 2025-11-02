<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\ExportQueue;
use App\Models\ExportQueueFile;
use App\Services\Actor\Traits\ZooniverseErrorNotification;
use App\Services\Csv\AwsS3CsvService;
use App\Services\Process\MapZooniverseCsvColumnsService;
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Job to build CSV manifest file for Zooniverse export.
 *
 * This job processes exported images from S3, creates a CSV manifest file,
 * and triggers the zip creation process. It handles:
 * - Listing and counting S3 objects
 * - Building CSV with mapped columns
 * - Validating row counts
 * - Triggering zip creation via SQS
 */
class ZooniverseExportBuildCsvJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZooniverseErrorNotification;

    public int $timeout = 1800;

    /**
     * Create a new job instance.
     *
     * @param  ExportQueue  $exportQueue  The export queue model instance
     */
    public function __construct(protected ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue->withoutRelations();
        $this->onQueue(config('config.queue.export'));
    }

    /**
     * Execute the job.
     *
     * Processes S3 objects, creates CSV manifest, and triggers zip creation.
     *
     * @param  S3Client  $s3  AWS S3 client
     * @param  SqsClient  $sqs  AWS SQS client
     * @param  AwsS3CsvService  $awsS3CsvService  Service for handling S3 CSV operations
     * @param  MapZooniverseCsvColumnsService  $mapZooniverseCsvColumnsService  Service for mapping CSV columns
     * @throws \Exception When no images are found or CSV row count doesn't match image count
     */
    public function handle(
        S3Client $s3,
        SqsClient $sqs,
        AwsS3CsvService $awsS3CsvService,
        MapZooniverseCsvColumnsService $mapZooniverseCsvColumnsService
    ): void {

        $this->exportQueue->load('expedition');

        // === CONFIG-BASED PATHS ===
        $scratchDir = config('config.scratch_dir'); // e.g. "scratch"
        $processDir = "{$this->exportQueue->id}-".config('zooniverse.actor_id')."-{$this->exportQueue->expedition->uuid}";
        $fullProcessPath = "{$scratchDir}/{$processDir}";
        $csvFilePath = "{$fullProcessPath}/manifest.csv";
        $s3Bucket = config('filesystems.disks.s3.bucket');

        // === LIST S3 OBJECTS + CALCULATE SIZE & COUNT ===
        $totalSize = 0;
        $fileCount = 0;
        $imageKeys = [];

        $paginator = $s3->getPaginator('ListObjectsV2', [
            'Bucket' => $s3Bucket,
            'Prefix' => "{$fullProcessPath}/",
        ]);

        foreach ($paginator as $page) {
            foreach ($page['Contents'] ?? [] as $object) {
                if (str_ends_with($object['Key'], '.jpg')) {
                    $totalSize += $object['Size'];
                    $fileCount++;
                    $imageKeys[] = $object['Key'];
                }
            }
        }

        if ($fileCount === 0) {
            throw new \Exception(t('No images found in S3 directory: %s', $fullProcessPath));
        }

        // === BUILD CSV ON S3 ===
        $awsS3CsvService->createBucketStream($s3Bucket, $csvFilePath, 'w');
        $awsS3CsvService->createCsvWriterFromStream();
        $awsS3CsvService->csv->addEncodingFormatter();

        $first = true;

        ExportQueueFile::where('queue_id', $this->exportQueue->id)
            ->chunk(config('services.aws.lambda_export_count'), function ($chunk) use (
                $imageKeys,
                $awsS3CsvService,
                $mapZooniverseCsvColumnsService,
                &$first,
                $fullProcessPath
            ) {
                $csvData = $chunk->filter(fn ($file) => in_array("{$fullProcessPath}/{$file->subject_id}.jpg", $imageKeys)
                )->map(fn ($file) => $mapZooniverseCsvColumnsService->mapColumns($file, $this->exportQueue)
                );

                if ($csvData->isEmpty()) {
                    return;
                }

                if ($first) {
                    $awsS3CsvService->csv->insertOne(array_keys((array) $csvData->first()));
                    $first = false;
                }

                $awsS3CsvService->csv->insertAll($csvData->toArray());
            });

        // === VALIDATE ROW COUNT ===
        $awsS3CsvService->createBucketStream($s3Bucket, $csvFilePath, 'r');
        $awsS3CsvService->createCsvReaderFromStream();
        $csvRowCount = $awsS3CsvService->csv->getReaderCount();

        // Subtract 1 for header row to get actual data row count
        $csvDataRowCount = $csvRowCount - 1;

        if ($csvDataRowCount !== $fileCount) {
            throw new \Exception(t('CSV row count (%s) does not match image count (%s).', $csvRowCount, $fileCount));
        }

        // === SEND EXACT MESSAGE TO ZIP TRIGGER ===
        $queueUrl = $sqs->getQueueUrl(['QueueName' => config('services.aws.queue_zip_trigger')])['QueueUrl'];
        $updatesQueueUrl = $sqs->getQueueUrl(['QueueName' => config('services.aws.queue_updates')])['QueueUrl'];

        $payload = [
            'processDir' => $processDir, // Now just the ID part without scratch/
            's3Bucket' => $s3Bucket,
            'updatesQueueUrl' => $updatesQueueUrl,
            'queueId' => $this->exportQueue->id,
            'totalSize' => $totalSize,
            'fileCount' => $fileCount,
        ];

        $sqs->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => json_encode($payload),
        ]);

        $this->exportQueue->stage = 3;
        $this->exportQueue->save();
    }

    /**
     * Handle a job failure.
     *
     * @param  Throwable  $throwable  The exception that caused the failure
     */
    public function failed(Throwable $throwable): void
    {
        $this->sendErrorNotification($this->exportQueue, $throwable);
    }
}
