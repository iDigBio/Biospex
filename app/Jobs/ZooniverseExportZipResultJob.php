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

use App\Models\ExportQueue;
use App\Models\User;
use App\Notifications\Generic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ZooniverseExportZipResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $data
    ) {
        $this->onQueue(config('config.queue.export'));
    }

    public function handle(): void
    {
        $queueId = $this->data['queueId'] ?? null;
        $status = $this->data['status'] ?? null;

        if (! $queueId || ! $status) {
            Log::warning('Invalid ZIP result payload', $this->data);

            return;
        }

        $queue = ExportQueue::find($queueId);
        if (! $queue) {
            Log::warning("ExportQueue #{$queueId} not found");

            return;
        }

        if ($status === 'zip-ready') {
            $this->handleZipSuccess($queue);
        } else {
            $this->handleZipFailure($queue, $this->data);
        }
    }

    private function handleZipSuccess(ExportQueue $queue): void
    {
        $queue->stage = 3;
        $queue->save();

        ZooniverseExportCreateReportJob::dispatch($queue)->onQueue(config('config.queue.export'));
    }

    private function handleZipFailure(ExportQueue $queue, array $data): void
    {
        $queue->error = 1;
        $queue->save();

        Log::error("ZIP failed for queue #{$queue->id}", $data);
    }

    /**
     * Handle a job failure — your exact pattern
     */
    public function failed(\Throwable $throwable): void
    {
        $queueId = $this->data['queueId'] ?? 'unknown';
        $queue = ExportQueue::find($queueId);

        if ($queue) {
            $queue->error = 1;
            $queue->save();
        }

        $attributes = [
            'subject' => t('Expedition Export Process Error'), 'html' => [
                t('Queue Id: %s', $queueId), t('Expedition Id: %s', $queue?->expedition_id ?? 'unknown'),
                t('File: %s', $throwable->getFile()), t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        if ($user) {
            $user->notify(new Generic($attributes));
        }
    }
}
