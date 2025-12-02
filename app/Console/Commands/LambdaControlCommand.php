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

use Aws\Lambda\LambdaClient;
use Illuminate\Console\Command;

class LambdaControlCommand extends Command
{
    protected $signature = 'app:lambda-control {lambda} {action=start}';

    protected $description = 'Control Lambda concurrency: start (restore) or stop (set to 0)';

    public function handle(): int
    {
        $lambdaName = $this->argument('lambda');
        $action = strtolower($this->argument('action'));

        $lambdas = config('services.aws.lambdas', []);

        if (! isset($lambdas[$lambdaName])) {
            $this->error("Lambda '{$lambdaName}' not found in config");

            return self::FAILURE;
        }

        $targetConcurrency = match ($action) {
            'stop' => 0,
            'start' => (int) $lambdas[$lambdaName],
            default => throw new \InvalidArgumentException("Action must be 'start' or 'stop'"),
        };

        $client = new LambdaClient([
            'region' => config('services.aws.region'),
            'version' => 'latest',
        ]);

        try {
            if ($targetConcurrency === 0) {
                $client->putFunctionConcurrency([
                    'FunctionName' => $lambdaName,
                    'ReservedConcurrentExecutions' => 0,
                ]);
                $this->info("Stopped {$lambdaName} — concurrency set to 0");
            } else {
                // Remove any existing concurrency limit first
                try {
                    $client->deleteFunctionConcurrency(['FunctionName' => $lambdaName]);
                } catch (\Exception $e) {
                    // Ignore if no concurrency limit exists
                }

                $client->putFunctionConcurrency([
                    'FunctionName' => $lambdaName,
                    'ReservedConcurrentExecutions' => $targetConcurrency,
                ]);
                $this->info("Started {$lambdaName} — concurrency set to {$targetConcurrency}");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
