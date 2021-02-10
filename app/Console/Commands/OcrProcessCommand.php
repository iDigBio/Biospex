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

namespace App\Console\Commands;

use App\Jobs\OcrTesseractJob;
use App\Services\Process\OcrService;
use Artisan;
use Illuminate\Console\Command;

/**
 * Class OcrProcessCommand
 *
 * @package App\Console\Commands
 */
class OcrProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ocrprocess:records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts ocr processing if queues exist.';

    /**
     * @var \App\Services\Process\OcrService
     */
    private $ocrService;

    /**
     * OcrProcessCommand constructor.
     *
     * @param \App\Services\Process\OcrService $ocrService
     */
    public function __construct(OcrService $ocrService) {
        parent::__construct();

        $this->ocrService = $ocrService;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $queue = $this->ocrService->getOcrQueueForOcrProcessCommand();

        if ($queue === null) {
            return;
        }

        if ($queue->processed === $queue->total) {

            $this->ocrService->complete($queue);
            Artisan::call('ocr:poll');

            return;
        }

        if ($queue->status === 1) {
            return;
        }

        $queue->total = $this->ocrService->getSubjectCountForOcr($queue->project_id, $queue->expedition_id);
        $queue->status = 1;
        $queue->processed = 0;
        $queue->save();

        $subjects = $this->ocrService->getSubjectCursorForOcr($queue->project_id, $queue->expedition_id);

        $subjects->each(function ($subject) use($queue) {
            OcrTesseractJob::dispatch($queue->id, $subject);
        });
    }
}
