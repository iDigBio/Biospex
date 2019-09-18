<?php

namespace App\Services\Process;

use App\Repositories\Interfaces\Expedition;
use App\Services\Api\NfnApiService;
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
     * @var \App\Services\Api\NfnApiService
     */
    private $apiService;

    /**
     * PusherEventService constructor.
     *
     * @param \App\Services\Model\EventTranscriptionService $eventTranscriptionService
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Services\Api\NfnApiService $apiService
     */
    public function __construct(
        EventTranscriptionService $eventTranscriptionService,
        Expedition $expeditionContract,
        NfnApiService $apiService)
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
        $subject = $this->apiService->getNfnSubject($data->subject_ids[0]);

        $expedition = $this->getExpeditionBySubject($subject);

        if ($expedition === null) {
            return;
        }

        $user = $data->user_id !== null ? $this->apiService->getNfnUser($data->user_id) : null;

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