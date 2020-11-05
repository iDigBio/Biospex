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
use App\Services\Process\EventTranscriptionProcess;
use \App\Services\Process\PusherTranscriptionProcess;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ZooniversePusherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * @var int|null
     */
    private $days;

    /**
     * @var int
     */
    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     * @param int|null $days
     */
    public function __construct(int $expeditionId, int $days = null)
    {
        $this->onQueue(config('config.reconcile_tube'));
        $this->expeditionId = $expeditionId;
        $this->days = $days;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Process\PusherTranscriptionProcess $pusherTranscriptionProcess
     * @param \App\Services\Process\EventTranscriptionProcess $eventTranscriptionProcess
     * @return void
     */
    public function handle(PusherTranscriptionProcess $pusherTranscriptionProcess, EventTranscriptionProcess $eventTranscriptionProcess)
    {
        if ($this->skipReconcile($this->expeditionId)) {
            return;
        }

        try
        {
            $expedition = $pusherTranscriptionProcess->getExpedition($this->expeditionId);

            $timestamp = isset($this->days) ? Carbon::now()->subDays($this->days) : Carbon::now()->subDays(3);

            $transcriptions = $pusherTranscriptionProcess->getTranscriptions($expedition->id, $timestamp);

            $transcriptions->each(function ($transcription) use ($pusherTranscriptionProcess, $eventTranscriptionProcess, $expedition) {
                $pusherTranscriptionProcess->processTranscripts($transcription, $expedition);
                $eventTranscriptionProcess->createEventTranscription($transcription->classification_id, $expedition->project_id, $transcription->user_name, $transcription->classification_finished_at);
            });

            return;
        }
        catch (Exception $e)
        {
            $user = User::find(1);
            $message = [
                'Message: ' => t('An error occurred while processing pusher job for Expedition Id: %s', $this->expeditionId),
                'File: ' => $e->getFile(),
                'Line: ' => $e->getLine(),
                'Error: ' => $e->getMessage(),
            ];
            $user->notify(new JobError(__FILE__, $message));

            return;
        }
    }
}
