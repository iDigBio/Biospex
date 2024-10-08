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

use App\Repositories\ProjectRepository;
use App\Services\Transcriptions\CreateWeDigBioTranscriptionService;

/**
 * Class WeDigBioEventService
 */
class WeDigBioEventService
{
    private CreateWeDigBioTranscriptionService $createWeDigBioTranscriptionService;

    private ProjectRepository $projectRepository;

    /**
     * BiospexEventService constructor.
     */
    public function __construct(
        CreateWeDigBioTranscriptionService $createWeDigBioTranscriptionService,
        ProjectRepository $projectRepository
    ) {
        $this->createWeDigBioTranscriptionService = $createWeDigBioTranscriptionService;
        $this->projectRepository = $projectRepository;
    }

    /**
     * Adds transcription to event for particular user.
     *
     * @see \App\Jobs\PanoptesPusherJob
     */
    public function process(array $data, int $projectId)
    {
        $project = $this->projectRepository->find($projectId);

        if ($project === null) {
            return;
        }

        $this->createWeDigBioTranscriptionService->createEventTranscription((int) $data['classification_id'], $projectId);
    }
}
