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

use App\Jobs\Traits\SkipNfn;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Transcriptions\CreateBiospexEventTranscriptionService;
use App\Services\Transcriptions\CreateWeDigBioTranscriptionService;
use App\Services\Transcriptions\UpdateOrCreatePusherTranscriptionService;
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
 *
 * @package App\Jobs
 */
class ZooniversePusherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * @var int
     */
    private int $expeditionId;

    /**
     * @var int|null
     */
    private ?int $days;

    /**
     * @var int
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     * @param int|null $days
     */
    public function __construct(int $expeditionId, int $days = null)
    {
        $this->onQueue(config('config.queues.reconcile'));
        $this->expeditionId = $expeditionId;
        $this->days = $days;
    }

    /**
     * Creates or updates existing pusher transcriptions from overnight scripts.
     * Creates event transcripting in mysql for user if one does not exist.
     *
     * TODO: Perhaps break this out into two jobs instead
     *
     * @param \App\Services\Transcriptions\UpdateOrCreatePusherTranscriptionService $updateOrCreatePusherTranscriptionService
     * @param \App\Services\Transcriptions\CreateBiospexEventTranscriptionService $createBiospexEventTranscriptionService
     * @param \App\Services\Transcriptions\CreateWeDigBioTranscriptionService $createWeDigBioTranscriptionService
     * @throws \Exception
     */
    public function handle(
        UpdateOrCreatePusherTranscriptionService $updateOrCreatePusherTranscriptionService, 
        CreateBiospexEventTranscriptionService $createBiospexEventTranscriptionService,
        CreateWeDigBioTranscriptionService $createWeDigBioTranscriptionService)
    {
        if ($this->skipReconcile($this->expeditionId)) {
            $this->delete();

            return;
        }

        $expedition = $updateOrCreatePusherTranscriptionService->getExpedition($this->expeditionId);
        if (!$expedition) {
            throw new Exception(t('Could not find Expedition using Id: %', $this->expeditionId));
        }

        $timestamp = isset($this->days) ? Carbon::now()->subDays($this->days) : Carbon::now()->subDays(3);

        $transcriptions = $updateOrCreatePusherTranscriptionService->getTranscriptions($expedition->id, $timestamp);

        $transcriptions->each(function ($transcription) use (
            $updateOrCreatePusherTranscriptionService,
            $createBiospexEventTranscriptionService,
            $createWeDigBioTranscriptionService,
            $expedition) {
            $updateOrCreatePusherTranscriptionService->processTranscripts($transcription, $expedition);
            $createBiospexEventTranscriptionService->createEventTranscription($transcription->classification_id, $expedition->project_id, $transcription->user_name, $transcription->classification_finished_at);
            $createWeDigBioTranscriptionService->createEventTranscription($transcription->classification_id, $expedition->project_id, $transcription->classification_finished_at);
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
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        $user = User::find(1);
        $messages = [
            t('Error: %s', $exception->getMessage()),
            t('File: %s', $exception->getFile()),
            t('Line: %s', $exception->getLine()),
        ];
        $user->notify(new JobError(__FILE__, $messages));
    }
}
