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
use App\Services\Process\PanoptesTranscriptionProcess;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Class ZooniverseTranscriptionJob
 *
 * @package App\Jobs
 */
class ZooniverseTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * Create a new job instance.
     *
     * @param int $expeditionId
     */
    public function __construct(int $expeditionId)
    {
        $this->onQueue(config('config.reconcile_tube'));
        $this->expeditionId = $expeditionId;
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\Process\PanoptesTranscriptionProcess $transcriptionProcess
     * @return void
     */
    public function handle(PanoptesTranscriptionProcess $transcriptionProcess)
    {
        if ($this->skipReconcile($this->expeditionId)) {
            return;
        }

        try {
            $transcriptDir = config('config.nfn_downloads_transcript');

            if (! Storage::exists($transcriptDir.'/'.$this->expeditionId.'.csv')) {
                $this->delete();

                return;
            }

            $csvFile = Storage::path($transcriptDir.'/'.$this->expeditionId.'.csv');
            $transcriptionProcess->process($csvFile, $this->expeditionId);

            if ($transcriptionProcess->checkCsvError()) {
                throw new Exception('Error in Classification transcript job.');
            }

            return;
        }
        catch (\Exception $e) {
            $user = User::find(1);
            $messages = [
                t('Error: %s', $e->getMessage()),
                t('File: %s', $e->getFile()),
                t('Line: %s', $e->getLine()),
            ];
            $user->notify(new JobError(__FILE__, $messages));

            return;
        }
    }
}
