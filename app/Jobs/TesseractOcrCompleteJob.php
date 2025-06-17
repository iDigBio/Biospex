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

use App\Models\OcrQueue;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\TesseractOcr\TesseractOcrService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class TesseractOcrCompleteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(protected OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue->withoutRelations();
        $this->onQueue(config('config.queue.lambda_ocr'));
    }

    /**
     * Execute the job.
     *
     * @throws \League\Csv\CannotInsertRecord
     */
    public function handle(TesseractOcrService $service): void
    {
        $this->ocrQueue->status = 1;
        $this->ocrQueue->save();

        $service->ocrCompleted($this->ocrQueue);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $this->ocrQueue->error = 1;
        $this->ocrQueue->save();

        $attributes = [
            'subject' => t('Ocr Process Error'),
            'html' => [
                t('Queue Id: %s', $this->ocrQueue->id),
                t('Project Id: %s', $this->ocrQueue->project_id),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];
        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
