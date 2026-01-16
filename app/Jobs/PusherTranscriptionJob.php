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

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Generic;
use App\Services\Transcriptions\PusherTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MongoDB\Driver\Exception\BulkWriteException;

/**
 * Job to handle Pusher transcription data processing.
 *
 * This job processes transcription data received from Pusher service, with retry logic
 * for failed attempts. It handles duplicate entry cases and notifies administrators
 * of failures.
 *
 * @implements ShouldQueue
 */
class PusherTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public int $timeout = 60;

    /**
     * Create a new job instance.
     *
     * @param  array  $dashboardData  The dashboard data to be processed
     */
    public function __construct(protected array $dashboardData)
    {
        $this->onQueue(config('config.queue.pusher_handler'));
    }

    /**
     * Execute the job.
     *
     * Processes the dashboard data through the transcription service.
     * Handles duplicate key errors by logging and stopping retries.
     *
     * @param  PusherTranscriptionService  $service  The service to process transcriptions
     *
     * @throws BulkWriteException When a non-duplicate write error occurs
     */
    public function handle(PusherTranscriptionService $service): void
    {
        try {
            $service->create($this->dashboardData);
        } catch (BulkWriteException $e) {
            if (str_contains($e->getMessage(), 'E11000 duplicate key error')) {
                $this->delete(); // Don't retry
            } else {
                throw $e; // Retry other errors
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * Notifies administrator about the failure with detailed error information.
     *
     * @param  \Throwable  $e  The exception that caused the failure
     */
    public function failed(\Throwable $e): void
    {
        $user = User::find(config('config.admin.user_id'));
        $user?->notify(new Generic([
            'subject' => 'Pusher Transcription Job Error',
            'html' => [
                "File: {$e->getFile()}",
                "Line: {$e->getLine()}",
                "Message: {$e->getMessage()}",
            ],
        ]));
    }
}
