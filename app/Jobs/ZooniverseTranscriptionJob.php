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

use App\Jobs\Traits\SkipZooniverse;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Transcriptions\CreatePanoptesTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Class ZooniverseTranscriptionJob
 *
 * @package App\Jobs
 */
class ZooniverseTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipZooniverse;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 300;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->onQueue(config('config.queue.reconcile'));
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Transcriptions\CreatePanoptesTranscriptionService $createPanoptesTranscriptionService
     * @throws \League\Csv\CannotInsertRecord
     */
    public function handle(CreatePanoptesTranscriptionService $createPanoptesTranscriptionService)
    {
        if ($this->skipReconcile($this->expeditionId)) {
            $this->delete();

            return;
        }

        $csvFilePath = config('zooniverse.directory.transcript') . "/{$this->expeditionId}.csv";
        if (! Storage::disk('s3')->exists($csvFilePath)) {
            $this->delete();

            return;
        }

        $createPanoptesTranscriptionService->process($csvFilePath, $this->expeditionId);

        $fileName = $createPanoptesTranscriptionService->checkCsvError();
        if ($fileName !== null) {
            $attributes = [
                'subject' => t('Zooniverse Transcription Job Error'),
                'html'    => [
                    t('File: %s', __FILE__),
                    t('Line: %s', 91)
                ],
            ];

            User::find(config('config.admin.user_id')->notify(new Generic($attributes)));
        }
    }

    /**
     * Prevent job overlap using expeditionId.
     *
     * @return \Illuminate\Queue\Middleware\WithoutOverlapping[]
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->expeditionId)];
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Zooniverse Transcription Job Failed'),
            'html'    => [
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        User::find(config('config.admin.user_id')->notify(new Generic($attributes)));
    }
}
