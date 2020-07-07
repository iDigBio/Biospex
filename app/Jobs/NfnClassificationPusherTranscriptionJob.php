<?php
/**
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
use App\Models\Traits\UuidTrait;
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Model\PusherTranscriptionService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;

class NfnClassificationPusherTranscriptionJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UuidTrait, SkipNfn;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * @var bool
     */
    private $command;

    /**
     * NfnClassificationPusherTranscriptionJob constructor.
     *
     * @param int $expeditionId
     * @param bool $command
     */
    public function __construct(int $expeditionId, $command = false)
    {
        $this->expeditionId = $expeditionId;
        $this->command = $command;
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Handle job.
     *
     * @param PusherTranscriptionService $pusherTranscriptionService
     */
    public function handle(
        PusherTranscriptionService $pusherTranscriptionService
    )
    {
        if ($this->skip($this->expeditionId)) {
            $this->delete();

            return;
        }

        try
        {
            $expedition = $pusherTranscriptionService->getExpedition($this->expeditionId);

            $timestamp = !$this->command ? Carbon::now()->subDays(3) : null;

            $transcriptions = $pusherTranscriptionService->getTranscriptions($expedition->id, $timestamp);

            $transcriptions->each(function ($transcription) use ($pusherTranscriptionService, $expedition) {
                $pusherTranscriptionService->processTranscripts($transcription, $expedition);
            });

            $this->delete();
        }
        catch (Exception $e)
        {
            $user = User::find(1);
            $message = [
                'Message: ' => trans('pages.nfn_pusher_job_error', [':expeditionId' => $this->expeditionId]),
                'File: ' => $e->getFile(),
                'Line: ' => $e->getLine(),
                'Error: ' => $e->getMessage(),
            ];
            $user->notify(new JobError(__FILE__, $message));

            $this->delete();

            return;
        }
    }
}
