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
use App\Models\User;
use App\Notifications\JobError;
use App\Services\Process\PanoptesTranscriptionProcess;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Storage;

class NfnClassificationTranscriptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 7200;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * @var bool
     */
    private $command;

    /**
     * @var array
     */
    private $nfnSkipReconcile = [];

    /**
     * NfnClassificationTranscriptJob constructor.
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
     * Execute the job.
     *
     * @param PanoptesTranscriptionProcess $transcriptionProcess
     * @return void
     */
    public function handle(
        PanoptesTranscriptionProcess $transcriptionProcess
    ) {

        if ($this->skip($this->expeditionId)) {
            $this->delete();

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

            if ($this->command) {
                $this->delete();

                return;
            }

            NfnClassificationPusherTranscriptionJob::dispatch($this->expeditionId);
        } catch (Exception $e) {
            $user = User::find(1);

            $message = [
                trans('pages.nfn_transcript_job_error', ['expeditionId' => $this->expeditionId]),
                $e->getMessage(),
                $e->getFile() . ':' . $e->getLine(),
                $transcriptionProcess->csvFile
            ];

            $user->notify(new JobError(__FILE__, $message));

            $this->delete();

            return;
        }
    }
}
