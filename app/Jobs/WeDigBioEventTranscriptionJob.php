<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Jobs;

use App\Models\Project;
use App\Services\WeDigBio\WeDigBioTranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Job to process WeDigBio event transcriptions.
 *
 * This job is responsible for creating WeDigBio event transcriptions from classification data.
 * It works in conjunction with the WeDigBioTranscriptionService to process and store
 * transcription data for WeDigBio events.
 *
 * @implements ShouldQueue
 */
class WeDigBioEventTranscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     * Handles WeDigBio event transcriptions
     *
     * @param  array  $data  Classification data to be processed
     * @param  int  $projectId  The ID of the project associated with the transcription
     * @return void
     *
     *@see \App\Jobs\ZooniversePusherHandlerJob
     */
    public function __construct(protected array $data, protected int $projectId)
    {
        $this->onQueue(config('config.queue.wedigbio_event'));
    }

    /**
     * Execute the job.
     *
     * Processes the classification data to create a WeDigBio event transcription.
     * If the project is not found, the job will be deleted without processing.
     *
     * @param  Project  $project  The Project model instance for dependency injection
     * @param  WeDigBioTranscriptionService  $weDigBioTranscriptionService  Service to handle transcription creation
     */
    public function handle(
        Project $project,
        WeDigBioTranscriptionService $weDigBioTranscriptionService): void
    {
        $project = $project->find($this->projectId);

        if ($project === null) {
            $this->delete();

            return;
        }

        $weDigBioTranscriptionService->createEventTranscription((int) $this->data['classification_id'], $project->id);
    }
}
