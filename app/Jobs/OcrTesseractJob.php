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

use App\Models\OcrQueue;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\OcrService;
use App\Services\Process\TesseractService;
use Artisan;
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
    public $timeout = 172800;

    /**
     * @var \App\Models\OcrQueue
     */
    private $ocrQueue;

    /**
     * OcrTesseractJob constructor.
     *
     * @param \App\Models\OcrQueue $ocrQueue
     */
    public function __construct(OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue;
        $this->onQueue(config('config.ocr_tube'));
    }

    /**
     * Execute tesseract job.
     *
     * @param \App\Services\Process\OcrService $service
     * @param \App\Services\Process\TesseractService $tesseract
     * @throws \League\Csv\CannotInsertRecord
     */
    public function handle(OcrService $service, TesseractService $tesseract)
    {
        $service->setDir($this->ocrQueue->id);

        $count = $service->getSubjectCount($this->ocrQueue->project_id, $this->ocrQueue->expedition_id);
        if ($count === 0) {
            $service->complete($this->ocrQueue);

            Artisan::call('ocrprocess:records');

            $this->delete();

            return;
        }

        event('ocr.reset', [$this->ocrQueue, $count]);

        $files = $service->getSubjectsToProcess($this->ocrQueue->project_id, $this->ocrQueue->expedition_id);

        foreach ($files as $file) {
            $tesseract->process($file, $service->folderPath);
            $this->ocrQueue->processed = $this->ocrQueue->processed + 1;
            $this->ocrQueue->save();
        }

        event('ocr.status', [$this->ocrQueue]);

        Artisan::call('ocrprocess:records');

        $this->delete();

        return;
    }

    /**
     * The job failed to process.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        event('ocr.error', $this->ocrQueue);

        $messages = [
            $this->ocrQueue->project->title,
            'Error processing ocr record '.$this->ocrQueue->id,
            'File: '.$exception->getFile(),
            'Message: '.$exception->getMessage(),
            'Line: '.$exception->getLine(),
        ];

        $user = User::find(1);
        $user->notify(new JobError(__FILE__, $messages));

        Artisan::call('ocrprocess:records');
    }
}
