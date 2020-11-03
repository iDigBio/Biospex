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
use App\Repositories\Interfaces\OcrQueue;
use Illuminate\Console\Command;

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
     * @var \App\Repositories\Interfaces\OcrQueue
     */
    private $ocrQueueContract;

    /**
     * OcrProcessCommand constructor.
     *
     * @param \App\Repositories\Interfaces\OcrQueue $ocrQueueContract
     */
    public function __construct(OcrQueue $ocrQueueContract) {
        parent::__construct();

        $this->ocrQueueContract = $ocrQueueContract;
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $queue = $this->ocrQueueContract->getOcrQueueForOcrProcessCommand();

        if ($queue === null || $queue->status === 1) {
            return;
        }

        OcrTesseractJob::dispatch($queue);
    }
}
