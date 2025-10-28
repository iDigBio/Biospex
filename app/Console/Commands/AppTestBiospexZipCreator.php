<?php

namespace App\Console\Commands;

use Aws\S3\S3Client;
use Illuminate\Console\Command;

class AppTestBiospexZipCreator extends Command
{
    protected $signature = 'app:export-trigger-zip {processDir}';

    protected $description = 'Trigger ZIP creation for a given processDir';

    public function handle()
    {
        $processDir = $this->argument('processDir');
        $s3Bucket = config('filesystems.disks.s3.bucket');
        $queueId = explode('-', $processDir)[1] ?? 'unknown';

        $s3Client = new S3Client([
            'region' => config('services.aws.region'),
            'version' => 'latest',
            'profile' => config('services.aws.profile'),
        ]);

        $sqsClient = new \Aws\Sqs\SqsClient([
            'region' => config('services.aws.region'),
            'version' => 'latest',
            'profile' => config('services.aws.profile'),
        ]);

        // Get trigger queue URL
        $queueUrl = $sqsClient->getQueueUrl(['QueueName' => 'export-zip-trigger-queue-local'])['QueueUrl'];

        // Compute total size of all objects in prefix
        $totalSize = 0;
        $prefix = "scratch/{$processDir}/";

        $this->info("Calculating size of s3://{$s3Bucket}/{$prefix}...");

        $paginator = $s3Client->getPaginator('ListObjectsV2', [
            'Bucket' => $s3Bucket,
            'Prefix' => $prefix,
        ]);

        foreach ($paginator as $page) {
            foreach ($page['Contents'] ?? [] as $object) {
                $totalSize += $object['Size'];
                $this->line("  + {$object['Key']} ({$object['Size']} bytes)");
            }
        }

        $this->newLine();
        $this->info('Total size: '.$this->formatBytes($totalSize));

        $payload = [
            'processDir' => $processDir,
            's3Bucket' => $s3Bucket,
            'updatesQueueUrl' => 'https://sqs.us-east-2.amazonaws.com/147899039648/export-updates-queue-local',
            'queueId' => $queueId,
            'totalSize' => $totalSize,  // ← SEND SIZE
            'fileCount' => $this->countFiles($s3Client, $s3Bucket, $prefix), // optional
        ];

        $sqsClient->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => json_encode($payload),
        ]);

        $this->info("ZIP trigger sent for: <info>{$processDir}</info>");
        $this->line("Monitor: s3://{$s3Bucket}/export/{$processDir}.zip");
        $this->line('Watch: export-updates-queue-local → status: "zip-ready"');
    }

    private function countFiles($s3Client, $bucket, $prefix)
    {
        $count = 0;
        $paginator = $s3Client->getPaginator('ListObjectsV2', [
            'Bucket' => $bucket,
            'Prefix' => $prefix,
        ]);
        foreach ($paginator as $page) {
            $count += count($page['Contents'] ?? []);
        }

        return $count;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
