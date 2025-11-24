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

use App\Jobs\ZooniverseExportImageUpdateJob;
use App\Models\ExportQueue;
use App\Services\SqsListenerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class SqsListenerExportImageTasksDlq extends Command
{
    /** @var string Command signature */
    protected $signature = 'export:monitor-dlq';

    /** @var string Command description */
    protected $description = 'Robust SQS listener for export DLQ with automatic recovery';

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
            $this->info('Starting Export DLQ Monitor...');
            $this->validateConfiguration();
            $this->runWorker();

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->service->handleCriticalError('Failed to start export DLQ monitor', $e, $this);

            return self::FAILURE;
        }
    }

    /**
     * Validate required AWS configuration settings.
     *
     * @throws \RuntimeException When the required configuration is missing
     */
    private function validateConfiguration(): void
    {
        $required = [
            'services.aws.queues.export_image_tasks_dlq' => 'AWS_SQS_EXPORT_IMAGE_TASKS_DLQ',
            'services.aws.credentials' => 'AWS_CREDENTIALS',
        ];

        foreach ($required as $key => $env) {
            if (empty(Config::get($key))) {
                throw new RuntimeException("Missing config: {$key} (env: {$env})");
            }
        }
    }

    /**
     * Run the worker loop.
     */
    private function runWorker(): void
    {
        $idleChecker = fn () => $this->hasActiveExports();
        $queueKey = 'export_image_tasks_dlq';
        $graceKey = 'services.aws.export_idle_grace';
        $routeCallback = fn ($body) => $this->recoverFailedMessage($body);

        $this->service->run($idleChecker, $queueKey, $graceKey, $routeCallback, $this);
    }

    /**
     * Recover a failed message by dispatching it as failed.
     *
     * @param  array  $data  Message data
     *
     * @throws \InvalidArgumentException When required fields are missing
     */
    private function recoverFailedMessage(array $data): void
    {
        $required = ['queueId', 'subjectId'];
        $missing = array_diff($required, array_keys($data));

        if (! empty($missing)) {
            throw new InvalidArgumentException('DLQ message missing required fields: '.implode(', ', $missing));
        }

        $queueId = $data['queueId'];
        $subjectId = $data['subjectId'];

        $this->warn("🔄 Recovering failed subject {$subjectId} from queue {$queueId}");

        ZooniverseExportImageUpdateJob::dispatch($data);

        Log::info('DLQ message recovered', [
            'queue_id' => $queueId,
            'subject_id' => $subjectId,
        ]);
    }

    /**
     * Check if an export is currently queued/active.
     *
     * @return bool True if queued export exists
     */
    private function hasActiveExports(): bool
    {
        return ExportQueue::where('queued', 1)->exists();
    }
}
