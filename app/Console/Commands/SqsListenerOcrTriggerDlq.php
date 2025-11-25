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

use App\Jobs\TesseractOcrUpdateJob;
use App\Services\SqsListenerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Throwable;

class SqsListenerOcrTriggerDlq extends Command
{
    protected $signature = 'ocr:listen-dlq';

    protected $description = 'Listen to OCR trigger DLQ and recover failed OCR tasks';

    public function __construct(protected SqsListenerService $service)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $this->info('Starting OCR Trigger DLQ Listener...');
            $this->validateConfiguration();
            $this->runWorker();

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->service->handleCriticalError('Failed to start OCR DLQ listener', $e, $this);
            return self::FAILURE;
        }
    }

    private function validateConfiguration(): void
    {
        $required = [
            'services.aws.queues.ocr_trigger_dlq' => 'AWS_SQS_OCR_TRIGGER_DLQ',
        ];

        foreach ($required as $key => $env) {
            if (empty(Config::get($key))) {
                throw new RuntimeException("Missing config: {$key} (env: {$env})");
            }
        }
    }

    private function runWorker(): void
    {
        $idleChecker = fn () => $this->hasPendingDlqMessages();
        $queueKey = 'ocr_trigger_dlq';
        $graceKey = 'services.aws.ocr_idle_grace';
        $routeCallback = fn ($body) => $this->recoverFailedMessage($body);

        $this->service->run($idleChecker, $queueKey, $graceKey, $routeCallback, $this);
    }

    private function recoverFailedMessage(array $data): void
    {
        $ocrQueueFileId = $data['ocrQueueFileId'] ?? null;
        $subjectId = $data['subjectId'] ?? 'unknown';

        $this->warn("Recovering failed OCR for subject {$subjectId} (file ID: {$ocrQueueFileId})");

        // Dispatch recovery job — updates DB with error text
        TesseractOcrUpdateJob::dispatch($data);
    }

    private function hasPendingDlqMessages(): bool
    {
        return $this->service->hasPendingMessages('ocr_trigger_dlq');
    }
}