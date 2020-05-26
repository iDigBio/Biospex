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

use App\Models\Traits\UuidTrait;
use App\Services\Model\PusherTranscriptionService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Carbon;

class NfnClassificationPusherTranscriptionsJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, UuidTrait;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1800;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $expeditionIds;

    /**
     * NfnClassificationPusherTranscriptionsJob constructor.
     *
     * @param $expeditionIds array
     */
    public function __construct($expeditionIds)
    {
        $this->expeditionIds = $expeditionIds;
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

        if (empty($this->expeditionIds))
        {
            $this->delete();

            return;
        }

        try
        {
            collect($this->expeditionIds)->each(function($expeditionId) use ($pusherTranscriptionService){
                $expedition = $pusherTranscriptionService->getExpedition($expeditionId);

                $timestamp = Carbon::now()->subDays(3);

                $transcriptions = $pusherTranscriptionService->getTranscriptions($expedition->id, $timestamp);

                $transcriptions->filter(function($transcription) use ($pusherTranscriptionService) {
                    return $pusherTranscriptionService->checkPusherTranscription($transcription);
                })->each(function ($transcription) use ($pusherTranscriptionService, $expedition) {
                    $pusherTranscriptionService->processTranscripts($transcription, $expedition);
                });
            });
        }
        catch (Exception $e)
        {
            $this->delete();
        }
    }
}
