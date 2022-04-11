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

namespace App\Services\Classifications;

use App\Models\PusherClassification;
use App\Repositories\PusherClassificationRepository;
use App\Repositories\PusherTranscriptionRepository;
use App\Services\Api\PanoptesApiService;
use Carbon\Carbon;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;
use Validator;

/**
 * Class PusherDashboardService
 *
 * @package App\Services\Process
 */
class PusherDashboardService
{
    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private PanoptesApiService $apiService;

    /**
     * @var \App\Repositories\PusherTranscriptionRepository
     */
    private PusherTranscriptionRepository $pusherTranscriptionRepo;

    /**
     * @var \App\Repositories\PusherClassificationRepository
     */
    private PusherClassificationRepository $pusherClassificationRepo;

    /**
     * PusherDashboardService constructor.
     *
     * @param \App\Services\Api\PanoptesApiService $apiService
     * @param \App\Repositories\PusherTranscriptionRepository $pusherTranscriptionRepo
     * @param \App\Repositories\PusherClassificationRepository $pusherClassificationRepo
     */
    public function __construct(
        PanoptesApiService $apiService,
        PusherTranscriptionRepository $pusherTranscriptionRepo,
        PusherClassificationRepository $pusherClassificationRepo
    )
    {
        $this->apiService = $apiService;
        $this->pusherTranscriptionRepo = $pusherTranscriptionRepo;
        $this->pusherClassificationRepo = $pusherClassificationRepo;
    }

    /**
     * Process pusher data for dashboard.
     * Store records temporarily in MySql DB until processed from cron and added to MongoDB.
     *
     * @param array $data
     * @param string $title
     * @throws \Exception
     */
    public function process(array $data, string $title)
    {
        $subject = $this->apiService->getPanoptesSubject($data['subject_ids'][0]);
        $user = $data['user_id'] !== null ? $this->apiService->getPanoptesUser($data['user_id']) : null;

        if ($subject === null) {
            return;
        }

        $values = $this->setDashboardData($title, $data, $subject, $user);

        $this->pusherClassificationRepo->updateOrCreate(['classification_id' => $values['classification_id']], ['data' => $values]);
    }

    /**
     * Build item for dashboard.
     * This is built during the posted data from Pusher
     * $this->buildItem($data, $workflow, $subject, $expedition);
     */
    #[ArrayShape(['classification_id'    => "int",
                  'project'              => "string",
                  'description'          => "string",
                  'guid'                 => "string",
                  'timestamp'            => "\Carbon\Carbon",
                  'subject'              => "array",
                  'contributor'          => "array",
                  'transcriptionContent' => "array",
                  'discretionaryState'   => "string"
    ])] private function setDashboardData(string $title, array $data, array $subject, array $user = null): array
    {

        $thumbnailUri = $this->setPusherThumbnailUri($data);

        return [
            'classification_id'    => (int) $data['classification_id'],
            'project'              => $title,
            'description'          => 'Classification Id ' . $data['classification_id'],
            'guid'                 => Uuid::uuid4()->toString(),
            'timestamp'            => Carbon::now(),
            'subject'              => [
                'link'         => $subject['metadata']['references'] ?? '',
                'thumbnailUri' => $thumbnailUri,
            ],
            'contributor'          => [
                'decimalLatitude'  => $data['geo']['latitude'],
                'decimalLongitude' => $data['geo']['longitude'],
                'ipAddress'        => '',
                'transcriber'      => $user === null ? '' : $user['login'],
                'physicalLocation' => [
                    'country'      => $data['geo']['country_name'],
                    'province'     => '',
                    'county'       => '',
                    'municipality' => $data['geo']['city_name'],
                    'locality'     => '',
                ],
            ],
            'transcriptionContent' => [
                'lat'          => '',
                'long'         => '',
                'country'      => $subject['metadata']['country'] ?? '',
                'province'     => $subject['metadata']['stateProvince'] ?? '',
                'county'       => $subject['metadata']['county'] ?? '',
                'municipality' => '',
                'locality'     => '',
                'date'         => '', // which date to use? transcription date is messy
                'collector'    => '',
                'taxon'        => $subject['metadata']['scientificName'] ?? '',
            ],
            'discretionaryState'   => 'Transcribed',
        ];
    }

    /**
     * Create dashboard item.
     * Uses classifications stored in database to relieve traffic on MongoDB.
     *
     * @param \App\Models\PusherClassification $pusherClassification
     */
    public function createDashboardRecord(PusherClassification $pusherClassification)
    {

        if ($this->validateTranscription($pusherClassification->classification_id)) {
            return;
        }

        $this->pusherTranscriptionRepo->create($pusherClassification->data);
    }

    /**
     * Determine image url.
     *
     * @param array $data
     * @return mixed
     */
    public function setPusherThumbnailUri(array $data): mixed
    {
        $imageUrl = $data['subject_urls'][0];

        return $imageUrl['image/jpeg'] ?? ($imageUrl['image/png'] ?? null);
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $classification_id
     * @return mixed
     */
    public function validateTranscription($classification_id): mixed
    {

        $rules = ['classification_id' => 'unique:mongodb.pusher_transcriptions,classification_id'];
        $values = ['classification_id' => $classification_id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if failed.
        return $validator->fails();
    }
}