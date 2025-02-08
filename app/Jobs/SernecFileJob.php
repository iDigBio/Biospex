<?php

/*
 * Copyright (C) 2015  Biospex
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
use App\Services\Csv\Csv;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;

/**
 * Handles the processing of a file by dispatching batch jobs for each row.
 *
 * Implements the ShouldQueue interface for asynchronous job processing.
 * Utilizes batching for efficient dispatch of row processing jobs.
 */
class SernecFileJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Constructor method for initializing the class with a file path and setting up the queue.
     *
     * @param  string  $filePath  The path of the file to be processed.
     * @return void
     */
    public function __construct(protected string $filePath)
    {
        $this->onQueue(config('config.queue.sernec_file'));
    }

    /**
     * Handles the processing of a CSV file by reading its rows and dispatching batch jobs for each row.
     *
     * @param  Csv  $csv  An instance of the Csv class used to read and process the file.
     *
     * @throws \Throwable
     */
    public function handle(Csv $csv): void
    {
        if (! file_exists($this->filePath)) {
            \Log::error("File not found: {$this->filePath}");
        }

        // Create a new batch instance
        $batch = Bus::batch([])->name("Processing rows for file: {$this->filePath}")->onQueue(config('config.queue.sernec_row'));

        $csv->readerCreateFromPath($this->filePath);
        foreach ($csv->reader as $row) {
            $batch->add(new SernecProcessRowJob($row));
        }

        // Dispatch the batch
        $batch->dispatch();
    }

    /**
     * Handle the failed job.
     *
     * This method is called when a job fails. It sends an email notification
     * to the admin user with the details of the failed job.
     *
     * @param  \Throwable  $exception  The exception that caused the job to fail.
     */
    public function failed(\Throwable $exception): void
    {
        $attributes = [
            'subject' => t('AmChartJob failed'),
            'html' => [
                t('File: %s', $exception->getFile()),
                t('Line: %s', $exception->getLine()),
                t('Message: %s', $exception->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
