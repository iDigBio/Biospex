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

/**
 * Command to purge all AWS SQS export queues for a specified environment.
 * Handles image-tasks, zip-trigger, and updates queues.
 */
class PurgeAwsQueues extends Command
{
    protected $signature = 'app:awsqueue-purge {env : loc, dev, or prod}';

    protected $description = 'Purge all aws queues for the given environment';

    /**
     * Create a new command instance.
     *
     * @param  \Aws\Sqs\SqsClient  $sqs  AWS SQS client instance
     */
    public function __construct(protected SqsClient $sqs)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int 0 for success, 1 for failure
     *
     * @throws \Exception When queue operations fail
     */
    public function handle()
    {
        $env = $this->argument('env');

        $validEnvs = ['loc', 'dev', 'prod'];
        if (! in_array($env, $validEnvs)) {
            $this->error('Invalid environment. Use: '.implode(', ', $validEnvs));

            return 1;
        }

        $queues = config('services.aws.sqs');

        $this->info("Purging queues for environment: <comment>{$env}</comment>");

        foreach ($queues as $queueName) {
            try {
                $urlResult = $this->sqs->getQueueUrl(['QueueName' => $queueName]);
                $queueUrl = $urlResult['QueueUrl'];

                $this->info("Purging <info>{$queueName}</info>...");
                $this->sqs->purgeQueue(['QueueUrl' => $queueUrl]);

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
