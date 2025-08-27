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

use App\Models\PanoptesProject;
use App\Models\WeDigBioProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job to process Panoptes Pusher data for workflow classifications.
 *
 * This job handles incoming Pusher data from Panoptes, validates the JSON format,
 * and processes workflow classifications for both Panoptes and WeDigBio projects.
 * It dispatches subsequent jobs for processing classifications and events.
 *
 * @implements ShouldQueue
 */
class ProcessPanoptesPusherDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly string $pusherData)
    {
        $this->onQueue(config('config.queue.pusher_process', 'default'));
    }

    /**
     * Execute the job to process Panoptes Pusher data.
     *
     * Decodes and validates JSON data, ensures it contains required workflow_id,
     * and processes the data through appropriate channels. Logs success and failures.
     *
     * @throws \InvalidArgumentException When JSON is invalid or not an array
     * @throws \Throwable When processing fails
     */
    public function handle(): void
    {
        try {
            $data = json_decode($this->pusherData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON: '.json_last_error_msg());
            }

            if (! is_array($data)) {
                throw new \InvalidArgumentException('Decoded JSON must be an array');
            }

            // If not workflow_id, return.
            if (! isset($data['workflow_id'])) {
                return;
            }

            $this->processData($data);

        } catch (\Throwable $e) {
            Log::error('Failed to process Panoptes Pusher data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Process the validated Panoptes data.
     *
     * Looks up related Panoptes and WeDigBio projects, dispatches classification
     * and event transcription jobs based on the project type and available data.
     *
     * @param  array  $data  The validated Panoptes pusher data
     */
    private function processData(array $data): void
    {
        $panoptesProject = PanoptesProject::where('panoptes_project_id', $data['project_id'])->where('panoptes_workflow_id', $data['workflow_id'])->first();

        $weDigBioProject = WeDigBioProject::where('panoptes_project_id', $data['project_id'])->where('panoptes_workflow_id', $data['workflow_id'])->first();

        if ($panoptesProject === null && $weDigBioProject === null) {
            $this->delete();

            return;
        }

        $title = $weDigBioProject === null ?
            $panoptesProject->title : $weDigBioProject->title;

        ZooniverseClassificationJob::dispatch($data, $title);

        if (isset($panoptesProject->expedition_id)) {
            EventTranscriptionJob::dispatch($data, $panoptesProject->expedition_id);
        }

        if (isset($panoptesProject->project_id)) {
            WeDigBioEventTranscriptionJob::dispatch($data, $panoptesProject->project_id);
        }
    }
}
