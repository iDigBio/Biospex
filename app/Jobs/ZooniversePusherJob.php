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

use App\Models\User;
use App\Notifications\Generic;
use App\Services\Event\EventTranscriptionService;
use App\Services\Transcriptions\UpdateOrCreatePusherTranscriptionService;
use App\Services\WeDigBio\WeDigBioTranscriptionService;
use App\Traits\SkipZooniverse;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Class ZooniversePusherJob
 */
class ZooniversePusherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipZooniverse;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $expeditionId, protected ?int $days = null)
    {
        $this->onQueue(config('config.queue.reconcile'));
    }

    /**
     * Creates or updates existing pusher transcriptions from overnight scripts.
     * Creates event transcripting in mysql for user if one does not exist.
     *
     * TODO: Perhaps break this out into two jobs instead
     *
     * @throws \Exception
     */
    public function handle(
        UpdateOrCreatePusherTranscriptionService $updateOrCreatePusherTranscriptionService,
        EventTranscriptionService $eventTranscriptionService,
        WeDigBioTranscriptionService $weDigBioTranscriptionService): void
    {
        if ($this->skipReconcile($this->expeditionId)) {
            $this->delete();

            return;
        }

        $expedition = $updateOrCreatePusherTranscriptionService->getExpedition($this->expeditionId);
        if (! $expedition) {
            throw new Exception(t('Could not find Expedition using Id: %', $this->expeditionId));
        }

        $timestamp = isset($this->days) ? Carbon::now()->subDays($this->days) : Carbon::now()->subDays(3);

        $transcriptions = $updateOrCreatePusherTranscriptionService->getTranscriptions($expedition->id, $timestamp);

        $transcriptions->each(function ($transcription) use (
            $updateOrCreatePusherTranscriptionService,
            $eventTranscriptionService,
            $weDigBioTranscriptionService,
            $expedition) {
            $updateOrCreatePusherTranscriptionService->processTranscripts($transcription, $expedition);
            $eventTranscriptionService->createEventTranscription($transcription->classification_id, $expedition->project_id, $transcription->user_name, $transcription->classification_finished_at);
            $weDigBioTranscriptionService->createEventTranscription($transcription->classification_id, $expedition->project_id, $transcription->classification_finished_at);
        });
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
     * @return void
     */
    public function failed(Throwable $throwable)
    {
        $attributes = [
            'subject' => t('Zooniverse Pusher Job Failed'),
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
