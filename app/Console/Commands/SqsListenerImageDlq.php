<?php

/*
 * Copyright (C) 2014 - 2026, Biospex
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

use App\Jobs\TesseractOcrUpdateJob;
use App\Jobs\ZooniverseExportImageUpdateJob;
use App\Models\ExportQueue; // Added
use App\Models\OcrQueue;    // Added
use App\Services\SqsListenerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SqsListenerImageDlq extends Command
{
    /** @var string Command signature */
    protected $signature = 'image:listen-dlq';

    /** @var string Command description */
    protected $description = 'Listen to the Image Trigger DLQ and mark records as failed in the DB';

    public function __construct(protected SqsListenerService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->validateConfiguration();

            // The DLQ listener stays active if there are messages in the DLQ
            // OR if any parent processes (Export or OCR) are still marked as queued.
            $idleChecker = function () {
                $hasDlqMessages = $this->service->hasPendingMessages('image_trigger_dlq');
                $hasActiveExports = ExportQueue::where('queued', 1)->where('error', 0)->exists();
                $hasActiveOcr = OcrQueue::where('queued', 1)->where('error', 0)->exists();

                return $hasDlqMessages || $hasActiveExports || $hasActiveOcr;
            };

            $queueKey = 'image_trigger_dlq';
            $graceKey = 'services.aws.export_idle_grace';
            $routeCallback = fn ($body) => $this->routeMessage($body);

            $this->service->run($idleChecker, $queueKey, $graceKey, $routeCallback, $this);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->service->handleCriticalError('Failed to start Image DLQ listener', $e, $this);

            return self::FAILURE;
        }
    }

    /**
     * Validate required configuration.
     */
    private function validateConfiguration(): void
    {
        // Verified: Using .sqs. to match your services.php
        $required = ['services.aws.sqs.image_trigger_dlq', 'services.aws.region'];
        foreach ($required as $key) {
            if (empty(Config::get($key))) {
                throw new RuntimeException("Missing configuration: {$key}");
            }
        }
    }

    /**
     * Route DLQ message to the appropriate Update Job.
     */
    private function routeMessage(array $data): void
    {
        $taskType = $data['taskType'] ?? 'unknown';

        // Inject failure metadata for the existing Update Jobs
        $data['status'] = 'failed';
        $data['error'] = 'DLQ: Message exceeded maximum retries in SQS (likely Fetcher timeout or invalid URL)';

        try {
            match ($taskType) {
                'export' => ZooniverseExportImageUpdateJob::dispatch($data),
                'ocr' => TesseractOcrUpdateJob::dispatch($data),
                default => throw new \InvalidArgumentException("Unknown taskType in DLQ: {$taskType}"),
            };
        } catch (Throwable $e) {
            Log::error('DLQ Listener failed to dispatch update job', [
                'taskType' => $taskType,
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }
}
