<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

use App\Services\Process\SnsImageExportResultProcess;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SnsImageExportJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $data)
    {
        $this->onQueue(config('config.queue.sns_image_export'));
    }

    /**
     * Execute the job.
     */
    public function handle(SnsImageExportResultProcess $snsImageExportResultProcess): void
    {
        \Log::info('SnsImageExportJob', $this->data);
        try {
            $requestPayload = $this->data['requestPayload'];
            $responsePayload = $this->data['responsePayload'];

            // If errorMessage, something really went bad with the lambda function.
            if (isset($responsePayload['errorMessage'])) {
                $snsImageExportResultProcess->handleErrorMessage($requestPayload, $responsePayload['errorMessage']);

                return;
            }

            $snsImageExportResultProcess->handleResponse($responsePayload['statusCode'], $responsePayload['body']);
        } catch (Throwable $throwable) {
            $snsImageExportResultProcess->handleErrorMessage($this->data['requestPayload'], $throwable->getMessage());
        }
    }
}
