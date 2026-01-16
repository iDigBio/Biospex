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

use App\Models\Expedition;
use App\Models\Project;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\TesseractOcr\TesseractOcrBuild;
use App\Services\Actor\TesseractOcr\TesseractOcrQueueService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class OcrCreateJob
 */
class TesseractOcrCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    /**
     * OcrCreateJob constructor.
     */
    public function __construct(protected Project $project, protected ?Expedition $expedition = null)
    {
        $this->project = $project->withoutRelations();
        $this->expedition = $expedition?->withoutRelations();
        $this->onQueue(config('config.queue.ocr'));
    }

    /**
     * Handle Job.
     *
     * @throws \Exception
     */
    public function handle(TesseractOcrBuild $tesseractOcrBuild, TesseractOcrQueueService $queueService): void
    {
        if (! config('config.ocr_enabled')) {
            return;
        }

        $total = $tesseractOcrBuild->getSubjectCountForOcr($this->project, $this->expedition);

        // If no subjects to OCR, return
        if ($total === 0) {
            return;
        }

        $ocrQueue = $tesseractOcrBuild->createOcrQueue($this->project, $this->expedition, $total);

        $tesseractOcrBuild->createOcrQueueFiles($ocrQueue, $this->project, $this->expedition);

        // Trigger processing immediately with race-condition safety
        $queueService->processNextQueue();

        $this->delete();
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Error creating OCR job.'),
            'html' => [
                t('Project Id: %s', $this->project->id),
                t('Expedition Id: %s', $this->expedition?->id),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
