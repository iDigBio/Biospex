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
use App\Services\Actor\TesseractOcr\TesseractOcrService;
use App\Services\Api\AwsLambdaApiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;
use Throwable;

/**
 * Class TesseractOcrProcessJob
 */
class TesseractOcrProcessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected OcrQueue $ocrQueue)
    {
        $this->ocrQueue = $ocrQueue->withoutRelations();
        $this->onQueue(config('config.queue.lambda_ocr'));
    }

    /**
     * Execute the job.
     */
    public function handle(
        TesseractOcrService $tesseractOcrService,
        AwsLambdaApiService $awsLambdaApiService
    ): void {

        $files = $tesseractOcrService->getUnprocessedOcrQueueFiles($this->ocrQueue->id, 100);

        // If processed files count is 0, update subjects in mongodb, send notification to user, and delete the queue
        if ($files->count() === 0) {
            TesseractOcrCompleteJob::dispatch($this->ocrQueue);
            $this->delete();

            return;
        }

        $files->each(function ($file) use ($awsLambdaApiService) {
            $filePath = config('zooniverse.directory.lambda-ocr').'/'.$file->subject_id.'.txt';
            if (Storage::disk('s3')->exists($filePath)) {
                $file->processed = 1;
                $file->save();

                return;
            }

            if ($file->tries < 3) {
                $file->increment('tries');
                $this->invoke($awsLambdaApiService, $file);

                return;
            }

            Storage::disk('s3')->put($filePath, t('Error: Exceeded maximum tries trying to read OCR.'));
            $file->processed = 1;
            $file->save();
        });
    }

    /**
     * Invoke the lambda function.
     */
    public function invoke(AwsLambdaApiService $awsLambdaApiService, $file): void
    {
        $awsLambdaApiService->lambdaInvokeAsync(config('config.aws.lambda_ocr_function'), [
            'bucket' => config('filesystems.disks.s3.bucket'),
            'key' => config('zooniverse.directory.lambda-ocr').'/'.$file->subject_id.'.txt',
            'file' => $file->id,
            'uri' => $file->access_uri,
        ]);
    }

    /**
     * The job failed to process.
     */
    public function failed(Throwable $throwable): void
    {
        $this->ocrQueue->error = 1;
        $this->ocrQueue->save();

        $attributes = [
            'subject' => t('Ocr Process Error'),
            'html' => [
                t('Queue Id: %s', $this->ocrQueue->id),
                t('Project Id: %s', $this->ocrQueue->project->id),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];
        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
