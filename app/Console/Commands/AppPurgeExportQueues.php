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

use Aws\Sqs\SqsClient;
use Illuminate\Console\Command;

class AppPurgeExportQueues extends Command
{
    protected $signature = 'app:awsqueue-purge {env : loc, dev, or prod}';

    protected $description = 'Purge all export queues (image-tasks, zip-trigger, updates) for the given environment';

    public function handle()
    {
        $env = $this->argument('env');

        $validEnvs = ['loc', 'dev', 'prod'];
        if (! in_array($env, $validEnvs)) {
            $this->error('Invalid environment. Use: '.implode(', ', $validEnvs));

            return 1;
        }

        $suffix = $env === 'loc' ? '-local' : '-'.$env;

        $queues = [
            "export-image-tasks-queue{$suffix}", "export-zip-trigger-queue{$suffix}", "export-updates-queue{$suffix}",
        ];

        $client = new SqsClient([
            'region' => config('services.aws.region', 'us-east-2'), 'version' => 'latest',
        ]);

        $this->info("Purging queues for environment: <comment>{$env}</comment>");

        foreach ($queues as $queueName) {
            try {
                $urlResult = $client->getQueueUrl(['QueueName' => $queueName]);
                $queueUrl = $urlResult['QueueUrl'];

                $this->line("Purging <info>{$queueName}</info>...");
                $client->purgeQueue(['QueueUrl' => $queueUrl]);

                $this->info("Purged: {$queueName}");
            } catch (\Exception $e) {
                $this->warn("Failed to purge {$queueName}: ".$e->getMessage());
            }
        }

        $this->newLine();
        $this->info("All queues purged for <comment>{$env}</comment>");

        return 0;
    }
}
