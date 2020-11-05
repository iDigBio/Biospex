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

namespace App\Services\Process;

use App\Services\Model\ExpeditionService;
use App\Services\Api\PanoptesApiService;

class PusherEventService
{
    /**
     * @var \App\Services\Process\EventTranscriptionProcess
     */
    private $eventTranscriptionProcess;

    /**
     * @var \App\Services\Model\ExpeditionService
     */
    private $expeditionService;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $apiService;

    /**
     * PusherEventService constructor.
     *
     * @param \App\Services\Process\EventTranscriptionProcess $eventTranscriptionProcess
     * @param \App\Services\Model\ExpeditionService $expeditionService
     * @param \App\Services\Api\PanoptesApiService $apiService
     */
    public function __construct(
        EventTranscriptionProcess $eventTranscriptionProcess,
        ExpeditionService $expeditionService,
        PanoptesApiService $apiService)
    {

        $this->eventTranscriptionProcess = $eventTranscriptionProcess;
        $this->expeditionService = $expeditionService;
        $this->apiService = $apiService;
    }

    /**
     * Process pusher data.
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

        $this->eventTranscriptionProcess->createEventTranscription((int) $data['classification_id'], $expedition->project_id, $user['login']);
    }

    /**
     * Get expedition.
     *
     * @param $subject
     * @return mixed|null
     */
    public function getExpeditionBySubject($subject)
    {
        if (empty($subject['metadata']['#expeditionId'])) {
            return null;
        }

        return $this->expeditionService->find($subject['metadata']['#expeditionId']);
    }
}