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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\User;
use App\Notifications\Generic;
use App\Services\Actor\TesseractOcr\TesseractOcrResponse;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SnsTesseractOcrJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $data)
    {
        $this->onQueue(config('config.queue.sns_tesseract_ocr'));
    }

    /**
     * Execute the job.
     */
    public function handle(TesseractOcrResponse $tesseractOcrResponse): void
    {
        try {
            $tesseractOcrResponse->process($this->data);
        } catch (Throwable $throwable) {
            $attributes = [
                'subject' => t('SnsTesseractOcrJob Failed'),
                'html' => [
                    t('SnsTesseractOcrJob failed for Queue File ID: %s', $this->data['responsePayload']['body']['file']),
                    t('Error: %s', $throwable->getMessage()),
                    t('File: %s', $throwable->getFile()),
                    t('Line: %s', $throwable->getLine()),
                ],
            ];

            $user = User::find(1);
            $user->notify(new Generic($attributes, true));
        }
    }
}
