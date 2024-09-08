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
use App\Services\Transcriptions\CreateBiospexEventTranscriptionService;

/**
 * Class BiospexEventService
 */
class BiospexEventService
{
    private CreateBiospexEventTranscriptionService $createBiospexEventTranscriptionService;

    private ExpeditionRepository $expeditionRepo;

    private PanoptesApiService $apiService;

    /**
     * BiospexEventService constructor.
     */
    public function __construct(
        CreateBiospexEventTranscriptionService $createBiospexEventTranscriptionService,
        ExpeditionRepository $expeditionRepo,
        PanoptesApiService $apiService)
    {

        $this->createBiospexEventTranscriptionService = $createBiospexEventTranscriptionService;
        $this->expeditionRepo = $expeditionRepo;
        $this->apiService = $apiService;
    }

    /**
     * Adds transcription to event for particular user.
     *
     * @see \App\Jobs\PanoptesPusherJob
     */
    public function process(array $data, int $expeditionId)
    {
        $expedition = $this->expeditionRepo->find($expeditionId);

        if ($expedition === null) {
            return;
        }

        $user = $data['user_id'] !== null ? $this->apiService->getPanoptesUser($data['user_id']) : null;

        if ($user === null) {
            return;
        }

        $this->createBiospexEventTranscriptionService->createEventTranscription((int) $data['classification_id'], $expedition->project_id, $user['login']);
    }
}
