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

use App\Repositories\Interfaces\Expedition;
use App\Services\Api\PanoptesApiService;
use App\Services\Model\EventTranscriptionService;

class PusherEventService
{
    /**
     * @var \App\Services\Model\EventTranscriptionService
     */
    private $eventTranscriptionService;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $apiService;

    /**
     * PusherEventService constructor.
     *
     * @param \App\Services\Model\EventTranscriptionService $eventTranscriptionService
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Services\Api\PanoptesApiService $apiService
     */
    public function __construct(
        EventTranscriptionService $eventTranscriptionService,
        Expedition $expeditionContract,
        PanoptesApiService $apiService)
    {

        $this->eventTranscriptionService = $eventTranscriptionService;
        $this->expeditionContract = $expeditionContract;
        $this->apiService = $apiService;
    }

    /**
     * Process pusher data.
     *
     * @param $data
     */
    public function process($data)
    {
        $subject = $this->apiService->getPanoptesSubject($data->subject_ids[0]);

        $expedition = $this->getExpeditionBySubject($subject);

        if ($expedition === null) {
            return;
        }

        $user = $data->user_id !== null ? $this->apiService->getPanoptesUser($data->user_id) : null;

        if ($user === null) {
            return;
        }

        $this->eventTranscriptionService->updateOrCreateEventTranscription($data, $expedition->project_id, $user);
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

        return $this->expeditionContract->find($subject['metadata']['#expeditionId']);
    }
}