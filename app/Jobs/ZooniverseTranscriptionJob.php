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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, SkipNfn;

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
        $this->onQueue(config('config.queues.reconcile'));
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

        $transcriptDir = config('config.nfn_downloads_transcript');

        if (! Storage::exists($transcriptDir.'/'.$this->expeditionId.'.csv')) {
            $this->delete();

            return;
        }

        $csvFile = Storage::path($transcriptDir.'/'.$this->expeditionId.'.csv');
        $createPanoptesTranscriptionService->process($csvFile, $this->expeditionId);

        $fileName = $createPanoptesTranscriptionService->checkCsvError();
        if ($fileName !== null) {
            $user = User::find(1);
            $messages = [
                t('Expedition Id: %s', $this->expeditionId),
                t('Error: ', 'NfnPanoptes Transcription'),
                t('File: %s', __FILE__),
                t('Line: %s', 89),
            ];
            $user->notify(new JobError(__FILE__, $messages, $fileName));
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
