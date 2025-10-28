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

namespace App\Console\Commands;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;

class AppTestBiospexImageProcess extends Command
{
    protected $signature = 'app:export-test-images {--queue-id=999}';

    protected $description = 'Send 3 known image URLs to export-image-tasks-queue-local and print processDir';

    protected $testUrls = [
        'https://data.cyverse.org/dav-anon/iplant/projects/magnoliagrandiFLORA/images/MISS_2018_4/MISS0076046/MISS0076046.JPG',
        'https://media01.symbiota.org/media/seinet/sernec/FSU/000094/000094340.jpg',
        'https://sernecportal.org/imglib/seinet/sernec/NCU_VascularPlants/NCU00441/NCU00441216_01.JPG',
    ];

    public function handle()
    {
        $queueId = $this->option('queue-id');
        $processDir = "test-{$queueId}-".uniqid();
        $s3Bucket = config('filesystems.disks.s3.bucket');

        $client = new SqsClient([
            'region' => config('services.aws.region'),
            'version' => 'latest',
            'profile' => config('services.aws.profile'),
        ]);

        $queueUrl = $this->getQueueUrl($client, 'export-image-tasks-queue-local');

        $this->info('Sending 3 test images to queue...');
        $this->info("processDir: <info>{$processDir}</info>");
        $this->info("queueId: {$queueId}");
        $this->info("s3Bucket: {$s3Bucket}");

        foreach ($this->testUrls as $index => $accessURI) {
            $subjectId = 'test-subject-'.($index + 1);

            $payload = [
                'processDir' => $processDir,
                'accessURI' => $accessURI,
                'subjectId' => $subjectId,
                's3Bucket' => $s3Bucket,
                'updatesQueueUrl' => 'https://sqs.us-east-2.amazonaws.com/147899039648/export-updates-queue-local',
                'queueId' => $queueId,
            ];

            try {
                $client->sendMessage([
                    'QueueUrl' => $queueUrl,
                    'MessageBody' => json_encode($payload),
                ]);

                $this->line("Sent: <info>{$subjectId}</info>");
            } catch (AwsException $e) {
                $this->error("Failed to send {$subjectId}: ".$e->getMessage());
            }
        }

        $this->newLine();
        $this->info('Images sent! Wait 30–60 seconds for processing.');
        $this->warn('Then run:');
        $this->line("   php artisan app:export-trigger-zip {$processDir}");
        $this->newLine();
        $this->info('Monitor:');
        $this->line("   • S3: s3://{$s3Bucket}/scratch/{$processDir}/");
        $this->line('   • SQS: export-updates-queue-local (status: success)');
        $this->line('   • CloudWatch: BiospexImageProcess');
    }

    private function getQueueUrl(SqsClient $client, string $queueName): string
    {
        try {
            $result = $client->getQueueUrl(['QueueName' => $queueName]);

            return $result['QueueUrl'];
        } catch (AwsException $e) {
            $this->error("Queue not found: {$queueName}");
            exit(1);
        }
    }
}
