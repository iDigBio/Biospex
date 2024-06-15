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

use App\Models\Subject;
use App\Services\Process\TesseractService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class OcrTesseractJob
 *
 * @package App\Jobs
 */
class OcrTesseractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    public $timeout = 900;

    /**
     * @var int
     */
    private $queueId;

    /**
     * @var \App\Models\Subject
     */
    private $subject;

    /**
     * OcrTesseractJob constructor.
     *
     * @param int $queueId
     * @param \App\Models\Subject $subject
     */
    public function __construct(int $queueId, Subject $subject)
    {
        $this->queueId = $queueId;
        $this->subject = $subject;
        $this->onQueue(config('config.queue.import'));
    }

    /**
     * Execute tesseract job.
     *
     * @param \App\Services\Process\TesseractService $tesseract
     */
    public function handle(TesseractService $tesseract): void
    {
        $tesseract->process($this->subject);
        $this->delete();
    }

    /**
     * The job failed to process.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(\Throwable $throwable): void
    {
        $this->subject->ocr = 'Error: processing tesseract ocr job.';
        $this->subject->save();

        $this->delete();
    }
}
