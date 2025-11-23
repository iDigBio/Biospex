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

use App\Services\SqsListenerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class SqsListenerReconciliationTriggerDlq extends Command
{
    /** @var string Command signature */
    protected $signature = 'reconciliation:monitor-dlq';

    /** @var string Command description */
    protected $description = 'Robust SQS listener for reconciliation trigger DLQ with email alerts';

    public function __construct(protected SqsListenerService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int Command exit code
     */
    public function handle(): int
    {
        try {
            $this->info('Starting Reconciliation Trigger DLQ Monitor...');
            $this->validateConfiguration();
            $this->runWorker();

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->service->handleCriticalError('Failed to start reconciliation DLQ monitor', $e, $this);

            return self::FAILURE;
        }
    }

    /**
     * Validate required AWS configuration settings.
     *
     * @throws \InvalidArgumentException When the required configuration is missing
     */
    private function validateConfiguration(): void
    {
        $required = [
            'services.aws.queues.reconciliation_trigger_dlq' => 'AWS_SQS_RECONCILIATION_TRIGGER_DLQ',
        ];

        foreach ($required as $key => $env) {
            if (empty(Config::get($key))) {
                throw new InvalidArgumentException("Missing config: {$key} (env: {$env})");
            }
        }
    }

    /**
     * Run the worker loop.
     */
    private function runWorker(): void
    {
        $idleChecker = fn () => $this->hasPendingDlqMessages();
        $queueKey = 'reconciliation_trigger_dlq';
        $graceKey = 'services.aws.reconcile_idle_grace';
        $routeCallback = fn ($body) => $this->alertOnFailedMessage($body);

        $this->service->run($idleChecker, $queueKey, $graceKey, $routeCallback, $this);
    }

    /**
     * Process a single DLQ message for alerting.
     *
     * @param  array  $data  Message data
     *
     * @throws \InvalidArgumentException When required fields are missing
     */
    private function alertOnFailedMessage(array $data): void
    {
        $expeditionId = $data['expeditionId'] ?? 'unknown';
        $error = $data['error'] ?? 'Unknown error';

        $this->warn("🚨 Alerting on failed reconciliation for expedition {$expeditionId}: {$error}");

        Log::error('Reconciliation trigger DLQ alert', [
            'expedition_id' => $expeditionId,
            'error' => $error,
            'data' => $data,
        ]);

        $this->service->sendEmail("Reconciliation trigger failed for expedition {$expeditionId}", null, [
            'expedition_id' => $expeditionId,
            'error' => $error,
            'data' => $data,
        ], false, $this);
    }

    /**
     * Check if the DLQ has pending messages.
     *
     * @return bool True if messages > 0
     */
    private function hasPendingDlqMessages(): bool
    {
        return $this->service->hasPendingMessages('reconciliation_trigger_dlq');
    }
}
