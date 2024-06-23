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
use App\Notifications\Generic;
use App\Services\Api\AwsLambdaApiService;
use App\Services\Ocr\TesseractOcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class TesseractOcrProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \App\Models\OcrQueue
     */
    private OcrQueue $ocrQueue;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\OcrQueue $ocrQueue
     */
    public function __construct(OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Execute the job.
     */
    public function handle(TesseractOcrService $tesseractOcrService, AwsLambdaApiService $awsLambdaApiService): void
    {
        try {
            $files = $tesseractOcrService->getUnprocessedOcrQueueFiles($this->ocrQueue->id);

            // If processed files count is 0, update subjects in mongodb, send notification to user, and delete the queue
            if ($files->count() === 0) {
                $tesseractOcrService->ocrCompleted($this->ocrQueue);
                $this->delete();
                return;
            }

            $files->each(function($file) use ($awsLambdaApiService){
                if ($file->tries < 3) {
                    $this->invoke($awsLambdaApiService, $file);
                    return;
                }

                $file->processed = 1;
                $file->message = t('Error: Excceded maximum tries.');
                $file->save();
            });

            return;
        } catch (\Throwable $throwable) {
            $this->ocrQueue->error = 1;
            $this->ocrQueue->save();

            $attributes = [
                'subject' => t('Ocr Process Error'),
                'html'    => [
                    t('Queue Id: %s', $this->ocrQueue->id),
                    t('Project Id: %s'.$this->ocrQueue->project->id),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                    t('Message: %s', $throwable->getMessage())
                ],
            ];
            $user = User::find(config('config.admin.user_id'));
            $user->notify(new Generic($attributes));

            $this->delete();

            return;
        }
    }

    /**
     * @param \App\Services\Api\AwsLambdaApiService $awsLambdaApiService
     * @param $file
     * @return void
     */
    function invoke(AwsLambdaApiService $awsLambdaApiService, $file): void
    {
        $awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_ocr_function'), [
            'env'        => config('config.app.env'),
            'id'         => $file->id,
            'queue_id'   => $file->queue_id,
            'subject_id' => $file->subject_id,
            'access_uri' => $file->access_uri,
        ]);

        $file->tries++;
        $file->save();
    }
}
