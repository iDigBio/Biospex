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

use App\Models\Expedition;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Transcriptions\CreatePanoptesTranscriptionService;
use App\Traits\SkipZooniverse;
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
 */
class ZooniverseTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipZooniverse;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Expedition $expedition)
    {
        $this->expedition = $expedition->withoutRelations();
        $this->onQueue(config('config.queue.reconcile'));
    }

    /**
     * Execute the job.
     *
     * @throws \League\Csv\CannotInsertRecord
     */
    public function handle(CreatePanoptesTranscriptionService $createPanoptesTranscriptionService): void
    {
        if ($this->skipReconcile($this->expedition->id)) {
            $this->delete();

            return;
        }

        $csvFilePath = config('zooniverse.directory.transcript')."/{$this->expedition->id}.csv";
        if (! Storage::disk('s3')->exists($csvFilePath)) {
            $this->delete();

            return;
        }

        $createPanoptesTranscriptionService->process($csvFilePath, $this->expedition->id);

        $fileName = $createPanoptesTranscriptionService->checkCsvError();
        if ($fileName !== null) {
            $attributes = [
                'subject' => t('Zooniverse Transcription Job Error'),
                'html' => [
                    t('File: %s', __FILE__),
                    t('Line: %s', 91),
                ],
            ];

            $user = User::find(config('config.admin.user_id'));
            $user->notify(new Generic($attributes));
        }
    }

    /**
     * Prevent job overlap using expeditionId.
     *
     * @return \Illuminate\Queue\Middleware\WithoutOverlapping[]
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->expedition->id)];
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Zooniverse Transcription Job Failed'),
            'html' => [
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
