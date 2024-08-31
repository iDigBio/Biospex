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

namespace App\Services\Event;

use App\Services\Models\ProjectModelService;
use App\Services\Transcriptions\CreateWeDigBioTranscriptionService;

/**
 * Class WeDigBioEventService
 *
 * @package App\Services\Process
 */
class WeDigBioEventService
{
    /**
     * BiospexEventService constructor.
     *
     * @param \App\Services\Transcriptions\CreateWeDigBioTranscriptionService $createWeDigBioTranscriptionService
     * @param \App\Services\Models\ProjectModelService $projectModelService
     */
    public function __construct(
        private readonly CreateWeDigBioTranscriptionService $createWeDigBioTranscriptionService,
        private readonly ProjectModelService $projectModelService
    )
    {}

    /**
     * Adds transcription to event for particular user.
     *
     * @param array $data
     * @param int $projectId
     * @see \App\Jobs\PanoptesPusherJob
     *
     */
    public function process(array $data, int $projectId)
    {
        $project = $this->projectModelService->findWithRelations($projectId);

        if ($project === null) {
            return;
        }

        $this->createWeDigBioTranscriptionService->createEventTranscription((int) $data['classification_id'], $projectId);
    }
}