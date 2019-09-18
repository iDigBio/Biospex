<?php

namespace App\Services\Process;

use App\Repositories\Interfaces\PusherTranscription;
use App\Services\Api\NfnApiService;
use DateHelper;
use Ramsey\Uuid\Uuid;

class PusherWeDigBioDashboardService
{
    /**
     * @var \App\Services\Api\NfnApiService
     */
    private $nfnApiService;

    /**
     * @var \App\Repositories\Interfaces\PusherTranscription
     */
    private $pusherTranscriptionContract;

    /**
     * PusherWeDigBioDashboardService constructor.
     *
     * @param \App\Services\Api\NfnApiService $nfnApiService
     * @param \App\Repositories\Interfaces\PusherTranscription $pusherTranscriptionContract
     */
    public function __construct(NfnApiService $nfnApiService, PusherTranscription $pusherTranscriptionContract)
    {
        $this->nfnApiService = $nfnApiService;
        $this->pusherTranscriptionContract = $pusherTranscriptionContract;
    }

    /**
     * Process pusher data for dashboard.
     *
     * @param $data
     * @throws \Exception
     */
    public function process($data)
    {
        $workflow = $this->nfnApiService->getNfnWorkflow($data->workflow_id);
        $subject = $this->nfnApiService->getNfnSubject($data->subject_ids[0]);
        $user = $data->user_id !== null ? $this->nfnApiService->getNfnUser($data->user_id) : null;

        if ($workflow === null || $subject === null) {
            return;
        }

        $this->createDashboardFromPusher($data, $workflow, $subject, $user);
    }

    /**
     * Build item for dashboard.
     * This is built during the posted data from Pusher
     * $this->buildItem($data, $workflow, $subject, $expedition);
     *
     * @param $data
     * @param $workflow
     * @param $subject
     * @param $user
     * @throws \Exception
     */
    private function createDashboardFromPusher($data, $workflow, $subject, $user)
    {
        $thumbnailUri = $this->setPusherThumbnailUri($data);

        $item = [
            'classification_id'    => $data->classification_id,
            'project'              => $workflow['display_name'],
            'description'          => 'Classification Id ' . $data->classification_id,
            'guid'                 => Uuid::uuid4()->toString(),
            'timestamp'            => DateHelper::newMongoDbDate(),
            'subject'              => [
                'link'         => isset($subject['metadata']['references']) ? $subject['metadata']['references'] : '',
                'thumbnailUri' => $thumbnailUri,
            ],
            'contributor'          => [
                'decimalLatitude'  => $data->geo->latitude,
                'decimalLongitude' => $data->geo->longitude,
                'ipAddress'        => '',
                'transcriber'      => $user['login'],
                'physicalLocation' => [
                    'country'      => $data->geo->country_name,
                    'province'     => '',
                    'county'       => '',
                    'municipality' => $data->geo->city_name,
                    'locality'     => '',
                ],
            ],
            'transcriptionContent' => [
                'lat'          => '',
                'long'         => '',
                'country'      => isset($subject['metadata']['country']) ? $subject['metadata']['country'] : '',
                'province'     => isset($subject['metadata']['stateProvince']) ? $subject['metadata']['stateProvince'] : '',
                'county'       => isset($subject['metadata']['county']) ? $subject['metadata']['county'] : '',
                'municipality' => '',
                'locality'     => '',
                'date'         => '', // which date to use? transcription date is messy
                'collector'    => '',
                'taxon'        => isset($subject['metadata']['scientificName']) ? $subject['metadata']['scientificName'] : '',
            ],
            'discretionaryState'   => 'Transcribed',
        ];

        $this->pusherTranscriptionContract->create($item);
    }

    /**
     * Determine image url.
     *
     * @param $data
     * @return mixed
     */
    public function setPusherThumbnailUri($data)
    {
        $imageUrl = (array) $data->subject_urls[0];

        return $imageUrl['image/jpeg'];
    }
}