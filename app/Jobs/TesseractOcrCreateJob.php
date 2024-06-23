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
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
namespace App\Jobs;

use App\Models\User;
use App\Notifications\Generic;
use App\Services\Ocr\TesseractOcrService;
use Artisan;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class OcrCreateJob
 *
 * @package App\Jobs
 */
class TesseractOcrCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public int $timeout = 3600;

    /**
     * @var int
     */
    private int $projectId;

    /**
     * @var int|null $expeditionId
     */
    private ?int $expeditionId;

    /**
     * OcrCreateJob constructor.
     *
     * @param int $projectId
     * @param int|null $expeditionId
     */
    public function __construct(int $projectId, int $expeditionId = null)
    {
        $this->projectId = $projectId;
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Handle Job.
     *
     * @param \App\Services\Ocr\TesseractOcrService $tesseractOcrService
     */
    public function handle(TesseractOcrService $tesseractOcrService): void
    {
        if (config('config.ocr_disable'))
            return;

        try {
            $total = $tesseractOcrService->getSubjectCountForOcr($this->projectId, $this->expeditionId);

            // If no subjects to OCR, return
            if ($total === 0)
                return;

            $ocrQueue = $tesseractOcrService->createOcrQueue($this->projectId, $this->expeditionId, ['total' => $total]);

            $tesseractOcrService->createOcrQueueFiles($ocrQueue->id, $this->projectId, $this->expeditionId);

        } catch (Exception $e) {
            $attributes = [
                'subject' => t('Error creating OCR job.'),
                'html'    => [
                    t('Project Id: %s', $this->projectId),
                    t('Expedition Id: %s', $this->expeditionId),
                    t('File: %s', $e->getFile()),
                    t('Line: %s', $e->getLine()),
                    t('Message: %s', $e->getMessage())
                ],
            ];

            $user = User::find(config('config.admin.user_id'));
            $user->notify(new Generic($attributes));
        }

        $this->delete();
    }
}
