<?php

namespace App\Services\Process;

use App\Repositories\Interfaces\PusherTranscription;
use App\Services\Api\PanoptesApiService;
use DateHelper;
use Ramsey\Uuid\Uuid;

class PusherWeDigBioDashboardService
{
    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $apiService;

    /**
     * @var \App\Repositories\Interfaces\PusherTranscription
     */
    private $pusherTranscriptionContract;

    /**
     * PusherWeDigBioDashboardService constructor.
     *
     * @param \App\Services\Api\PanoptesApiService $apiService
     * @param \App\Repositories\Interfaces\PusherTranscription $pusherTranscriptionContract
     */
    public function __construct(PanoptesApiService $apiService, PusherTranscription $pusherTranscriptionContract)
    {
        $this->apiService = $apiService;
        $this->pusherTranscriptionContract = $pusherTranscriptionContract;
    }

    /**
     * Process pusher data for dashboard.
     *
     * @param $data
     * @param $panoptesProject
     * @throws \Exception
     */
    public function process($data, $panoptesProject)
    {
        $subject = $this->apiService->getPanoptesSubject($data->subject_ids[0]);
        $user = $data->user_id !== null ? $this->apiService->getPanoptesUser($data->user_id) : null;

        if ($subject === null) {
            return;
        }

        $this->createDashboardFromPusher($data, $subject, $user, $panoptesProject);
    }

    /**
     * Build item for dashboard.
     * This is built during the posted data from Pusher
     * $this->buildItem($data, $workflow, $subject, $expedition);
     *
     * @param $data
     * @param $subject
     * @param $user
     * @param $panoptesProject
     * @throws \Exception
     */
    private function createDashboardFromPusher($data, $subject, $user, $panoptesProject)
    {
        $thumbnailUri = $this->setPusherThumbnailUri($data);

        $value = [
            'classification_id'    => $data->classification_id,
        ];

        $attributes = [
            'classification_id'    => $data->classification_id,
            'project'              => $panoptesProject->title,
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

        $this->pusherTranscriptionContract->firstOrCreate($value, $attributes);
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