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

use App\Models\ExportQueue;
use App\Services\Actors\NfnPanoptes\Traits\NfnErrorNotification;
use App\Services\Api\AwsLambdaApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Class ZooniverseExportLambdaJob
 */
class ZooniverseExportLambdaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NfnErrorNotification;

    /**
     * @var \App\Models\ExportQueue
     */
    private ExportQueue $exportQueue;

    /**
     * @var \Illuminate\Support\Collection
     */
    private Collection $data;

    /**
     * @var bool
     */
    private bool $complete;

    /**
     * @var int
     */
    public int $timeout = 1800;

    /**
     * Indicate if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public bool $failOnTimeout = true;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\ExportQueue $exportQueue
     * @param \Illuminate\Support\Collection $data
     * @param bool $complete
     */
    public function __construct(ExportQueue $exportQueue, Collection $data, bool $complete = false)
    {
        $this->exportQueue = $exportQueue;
        $this->data = $data;
        $this->complete = $complete;
        $this->onQueue(config('config.queues.lambda'));
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Api\AwsLambdaApiService $awsLambdaApiService
     * @return void
     * @throws \Exception
     */
    public function handle(
        AwsLambdaApiService $awsLambdaApiService,
    ) {
        $this->data->each(function ($attributes) use ($awsLambdaApiService) {
            $awsLambdaApiService->lambdaInvokeAsync(config('config.aws_lambda_export_function'), $attributes);
        });

        if ($this->complete) {
            $this->exportQueue->stage = 3;
            $this->exportQueue->save();
            ZooniverseExportCheckImageProcessJob::dispatch($this->exportQueue)->delay(config('config.aws_lambda_delay'));
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        exec("sudo systemctl restart beanstalkd");
        $this->sendErrorNotification($this->exportQueue, $exception);
    }
}
