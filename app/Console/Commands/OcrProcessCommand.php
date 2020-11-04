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
use App\Services\Model\OcrQueueService;
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
     * @var \App\Services\Model\OcrQueueService
     */
    private $ocrQueueService;

    /**
     * OcrProcessCommand constructor.
     *
     * @param \App\Services\Model\OcrQueueService $ocrQueueService
     */
    public function __construct(OcrQueueService $ocrQueueService) {
        parent::__construct();

        $this->ocrQueueService = $ocrQueueService;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $queue = $this->ocrQueueService->getOcrQueueForOcrProcessCommand();

        if ($queue === null || $queue->status === 1) {
            return;
        }

        OcrTesseractJob::dispatch($queue);
    }
}
