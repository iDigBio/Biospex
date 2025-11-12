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

namespace App\Services\Actor\Zooniverse;

use App\Models\ExportQueue;
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;

/**
 * Service for handling Zooniverse ZIP trigger operations via AWS SQS.
 */
class ZooniverseZipTriggerService
{
    /**
     * Get complete export data including paths and S3 file information.
     *
     * @return array [totalSize, fileCount, imageKeys, fullProcessPath, csvFilePath, s3Bucket]
     *
     * @throws \Exception When no images are found
     */
    public function getExportData(S3Client $s3, ExportQueue $exportQueue): array
    {
        // Build all paths
        $scratchDir = config('config.scratch_dir');
        $processDir = $this->buildProcessDir($exportQueue);
        $fullProcessPath = "{$scratchDir}/{$processDir}";
        $csvFilePath = "{$fullProcessPath}/manifest.csv";
        $s3Bucket = config('filesystems.disks.s3.bucket');

        // Get S3 file data
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
            throw new \Exception("No images found in S3 directory: {$fullProcessPath}");
        }

        return [
            'totalSize' => $totalSize,
            'fileCount' => $fileCount,
            'imageKeys' => $imageKeys,
            'fullProcessPath' => $fullProcessPath,
            'csvFilePath' => $csvFilePath,
            's3Bucket' => $s3Bucket,
        ];
    }

    /**
     * Send ZIP trigger message to AWS SQS queue.
     *
     * @param  SqsClient  $sqs  AWS SQS client
     * @param  ExportQueue  $exportQueue  The export queue instance
     * @param  int  $totalSize  Total file size in bytes
     * @param  int  $fileCount  Number of files
     *
     * @throws \Exception When unable to send message
     */
    public function sendZipTrigger(SqsClient $sqs, ExportQueue $exportQueue, int $totalSize, int $fileCount): void
    {
        // Get queue URLs
        $queueUrl = $this->getQueueUrl($sqs, 'queue_zip_trigger');
        $updatesQueueUrl = $this->getQueueUrl($sqs, 'queue_export_update');

        // Build process directory path
        $processDir = $this->buildProcessDir($exportQueue);

        // Prepare payload
        $payload = [
            'processDir' => $processDir,
            's3Bucket' => config('filesystems.disks.s3.bucket'),
            'updatesQueueUrl' => $updatesQueueUrl,
            'queueId' => $exportQueue->id,
            'totalSize' => $totalSize,
            'fileCount' => $fileCount,
        ];

        // Send message to SQS
        $sqs->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => json_encode($payload),
        ]);
    }

    /**
     * Process complete ZIP trigger workflow: get export data and send trigger.
     *
     * @return array Export data for other uses
     *
     * @throws \Exception
     */
    public function processZipTrigger(SqsClient $sqs, S3Client $s3, ExportQueue $exportQueue): array
    {
        $exportData = $this->getExportData($s3, $exportQueue);

        $this->sendZipTrigger($sqs, $exportQueue, $exportData['totalSize'], $exportData['fileCount']);

        return $exportData;
    }

    /**
     * Build the process directory name for the export queue.
     */
    protected function buildProcessDir(ExportQueue $exportQueue): string
    {
        return "{$exportQueue->id}-".config('zooniverse.actor_id')."-{$exportQueue->expedition->uuid}";
    }

    /**
     * Get AWS SQS queue URL for given queue key.
     *
     * @param  SqsClient  $sqs  AWS SQS client instance
     * @param  string  $key  Queue configuration key
     * @return string Queue URL
     */
    protected function getQueueUrl(SqsClient $sqs, string $key): string
    {
        $queueName = config("services.aws.{$key}");

        return $sqs->getQueueUrl(['QueueName' => $queueName])['QueueUrl'];
    }
}
