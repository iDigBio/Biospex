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
use App\Services\Process\OcrService;
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
class OcrCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 3600;

    /**
     * @var
     */
    private $projectId;

    /**
     * @var null
     */
    private $expeditionId;

    /**
     * OcrCreateJob constructor.
     *
     * @param $projectId
     * @param null $expeditionId
     */
    public function __construct($projectId, $expeditionId = null)
    {
        $this->projectId = $projectId;
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Handle Job.
     *
     * @param \App\Services\Process\OcrService $ocrService
     */
    public function handle(OcrService $ocrService)
    {
        if (config('config.ocr_disable')) {
            $this->delete();

            return;
        }

        try {

            $total = $ocrService->getSubjectCountForOcr($this->projectId, $this->expeditionId);

            if ($total === 0) {
                $this->delete();

                return;
            }

            $queue = $ocrService->createOcrQueue($this->projectId, $this->expeditionId, ['total' => $total]);
            if (! $queue) {
                $this->delete();

                return;
            }

            Artisan::call('ocrprocess:records');

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

            User::find(config('config.admin.user_id'))->notify(new Generic($attributes));
        }

        $this->delete();
    }
}
