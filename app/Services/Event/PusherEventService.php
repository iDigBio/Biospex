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

use App\Repositories\ExpeditionRepository;
use App\Services\Api\PanoptesApiService;
use App\Services\Transcriptions\CreateEventTranscriptionService;

/**
 * Class PusherEventService
 *
 * @package App\Services\Process
 */
class PusherEventService
{
    /**
     * @var \App\Services\Transcriptions\CreateEventTranscriptionService
     */
    private CreateEventTranscriptionService $createEventTranscriptionService;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepo;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private PanoptesApiService $apiService;

    /**
     * PusherEventService constructor.
     *
     * @param \App\Services\Transcriptions\CreateEventTranscriptionService $createEventTranscriptionService
     * @param \App\Repositories\ExpeditionRepository $expeditionRepo
     * @param \App\Services\Api\PanoptesApiService $apiService
     */
    public function __construct(
        CreateEventTranscriptionService $createEventTranscriptionService,
        ExpeditionRepository $expeditionRepo,
        PanoptesApiService $apiService)
    {

        $this->createEventTranscriptionService = $createEventTranscriptionService;
        $this->expeditionRepo = $expeditionRepo;
        $this->apiService = $apiService;
    }

    /**
     * Adds transcription to event for particular user.
     * @see \App\Jobs\PanoptesPusherJob
     *
     * @param array $data
     */
    public function process(array $data)
    {
        $subject = $this->apiService->getPanoptesSubject($data['subject_ids'][0]);

        $expedition = $this->getExpeditionBySubject($subject);

        if ($expedition === null) {
            return;
        }

        $user = $data['user_id'] !== null ? $this->apiService->getPanoptesUser($data['user_id']) : null;

        if ($user === null) {
            return;
        }

        $this->createEventTranscriptionService->createEventTranscription((int) $data['classification_id'], $expedition->project_id, $user['login']);
    }

    /**
     * Get expedition.
     *
     * @param $subject
     * @return mixed|null
     */
    public function getExpeditionBySubject($subject): mixed
    {
        if (empty($subject['metadata']['#expeditionId'])) {
            return null;
        }

        return $this->expeditionRepo->find($subject['metadata']['#expeditionId']);
    }
}