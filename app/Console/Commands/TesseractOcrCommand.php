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

use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\TesseractOcr\TesseractOcrQueueService;
use Illuminate\Console\Command;
use Throwable;

class TesseractOcrCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tesseract:ocr-process {--reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks queue and processes OCR jobs.';

    /**
     * Create a new command instance.
     * Command is called after queue is created and while processing.
     *
     * @see \App\Services\Actor\TesseractOcr\TesseractOcrProcess
     * @see \App\Jobs\TesseractOcrCreateJob
     */
    public function __construct(protected TesseractOcrQueueService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        if (! config('config.ocr_enabled')) {
            return;
        }

        try {
            // 1. Check for finished batches
            $this->service->checkActiveQueuesForCompletion();

            // 2. Start the next batch if one is ready
            $this->service->processNextQueue($this->option('reset'));
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    /**
     * Handle the command failure.
     */
    public function fail(Throwable|string|null $exception = null): void
    {
        $message = $exception instanceof Throwable
            ? $exception->getMessage()
            : ($exception ?? 'Command failed');

        $attributes = [
            'subject' => t('TesseractOcrCommand Failed'),
            'html' => [
                t('Message: %s', $message),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user?->notify(new Generic($attributes));
    }
}
