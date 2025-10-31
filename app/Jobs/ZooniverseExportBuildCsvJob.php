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

class ZooniverseExportBuildCsvJob implements ShouldBeUnique, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ZooniverseErrorNotification;

    public int $timeout = 1800;

    public function __construct(protected ExportQueue $exportQueue)
    {
        $this->exportQueue = $exportQueue->withoutRelations();
        $this->onQueue(config('config.queue.export'));
    }

    public function handle(
        S3Client $s3,
        SqsClient $sqs,
        AwsS3CsvService $awsS3CsvService,
        MapZooniverseCsvColumnsService $mapZooniverseCsvColumnsService
    ): void {
        $this->exportQueue->load('expedition');

        // === CONFIG-BASED PATHS ===
        $scratchDir = config('scratch_dir'); // e.g. "scratch"
        $processDir = "{$scratchDir}/{$this->exportQueue->id}-".config('zooniverse.actor_id')."-{$this->exportQueue->expedition->uuid}";
        $csvFilePath = "{$processDir}/manifest.csv";
        $s3Bucket = config('filesystems.disks.s3.bucket');

        // === LIST S3 OBJECTS + CALCULATE SIZE & COUNT ===
        $totalSize = 0;
        $fileCount = 0;
        $imageKeys = [];

        $paginator = $s3->getPaginator('ListObjectsV2', [
            'Bucket' => $s3Bucket,
            'Prefix' => "{$processDir}/",
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
            throw new \Exception(t('No images found in S3 directory: %s', $processDir));
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
                $processDir
            ) {
                $csvData = $chunk->filter(fn ($file) => in_array("{$processDir}/{$file->subject_id}.jpg", $imageKeys)
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

        if ($csvRowCount !== $fileCount) {
            throw new \Exception(t('CSV row count (%s) does not match image count (%s).', $csvRowCount, $fileCount));
        }

        // === SEND EXACT MESSAGE TO ZIP TRIGGER ===
        $queueUrl = $sqs->getQueueUrl(['QueueName' => 'queue_zip_trigger'])['QueueUrl'];
        $updatesQueueUrl = $sqs->getQueueUrl(['QueueName' => 'queue_updates'])['QueueUrl'];

        $payload = [
            'processDir' => $processDir,
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

    public function failed(Throwable $throwable): void
    {
        $this->sendErrorNotification($this->exportQueue, $throwable);
    }
}
