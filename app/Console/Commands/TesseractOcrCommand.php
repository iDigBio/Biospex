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

use App\Jobs\TesseractOcrProcessJob;
use App\Services\Actor\TesseractOcr\TesseractOcrService;
use Illuminate\Console\Command;

class TesseractOcrCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tesseract:ocr-process {--reset}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks queue and processes OCR jobs.';

    /**
     * Create a new command instance.
     * Command is called after queue is created and while processing.
     *
     * @see \App\Services\Actor\TesseractOcr\TesseractOcrProcess
     * @see \App\Jobs\TesseractOcrCreateJob
     */
    public function __construct(protected TesseractOcrService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // If reset is true, it will return first in the queue whether it's error or not.
        $queue = $this->option('reset') ?
            $this->service->getFirstQueue(true) :
            $this->service->getFirstQueue();

        if ($queue === null) {
            return;
        }

        // Resetting the queue if it's not null and reset is true.
        if ($this->option('reset')) {
            $queue->status = 0;
            $queue->error = 0;
            $queue->save();
        }

        TesseractOcrProcessJob::dispatch($queue);
    }
}
