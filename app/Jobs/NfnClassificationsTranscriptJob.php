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

use App\Repositories\Interfaces\User;
use App\Notifications\JobError;
use App\Services\Process\PanoptesTranscriptionProcess;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Storage;

class NfnClassificationsTranscriptJob implements ShouldQueue
{

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var array
     */
    private $expeditionIds;

    /**
     * NfnClassificationsTranscriptJob constructor.
     *
     * @param array $expeditionIds
     */
    public function __construct(array $expeditionIds = [])
    {
        $this->expeditionIds = collect($expeditionIds);
        $this->onQueue(config('config.classification_tube'));
    }

    /**
     * Execute the job.
     *
     * @param PanoptesTranscriptionProcess $transcriptionProcess
     * @param User $userContract
     * @return void
     */
    public function handle(
        PanoptesTranscriptionProcess $transcriptionProcess,
        User $userContract
    )
    {
        if ($this->expeditionIds->isEmpty())
        {
            $this->delete();

            return;
        }

        try
        {
            $transcriptDir = config('config.nfn_downloads_transcript');

            $this->expeditionIds->filter(function($expeditionId) use ($transcriptDir) {
                return Storage::exists($transcriptDir . '/' . $expeditionId . '.csv');
            })->each(function($expeditionId) use ($transcriptionProcess, $transcriptDir) {
                $csvFile = Storage::path($transcriptDir . '/' . $expeditionId . '.csv');
                $transcriptionProcess->process($csvFile, $expeditionId);
            });

            if ( ! empty($transcriptionProcess->getCsvError()))
            {
                $user = $userContract->find(1);
                $user->notify(new JobError(__FILE__, $transcriptionProcess->getCsvError()));
            }

            NfnClassificationPusherTranscriptionsJob::dispatch($this->expeditionIds);
        }
        catch (Exception $e)
        {
            return;
        }
    }
}
