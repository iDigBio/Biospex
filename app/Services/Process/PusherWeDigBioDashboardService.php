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

namespace App\Services\Process;

use App\Models\PanoptesProject;
use App\Services\Model\PusherTranscriptionService;
use App\Services\Api\PanoptesApiService;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Validator;

/**
 * Class PusherWeDigBioDashboardService
 *
 * @package App\Services\Process
 */
class PusherWeDigBioDashboardService
{
    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private $apiService;

    /**
     * @var \App\Services\Model\PusherTranscriptionService
     */
    private $pusherTranscriptionService;

    /**
     * PusherWeDigBioDashboardService constructor.
     *
     * @param \App\Services\Api\PanoptesApiService $apiService
     * @param \App\Services\Model\PusherTranscriptionService $pusherTranscriptionService
     */
    public function __construct(PanoptesApiService $apiService, PusherTranscriptionService $pusherTranscriptionService)
    {
        $this->apiService = $apiService;
        $this->pusherTranscriptionService = $pusherTranscriptionService;
    }

    /**
     * Process pusher data for dashboard.
     *
     * @param array $data
     * @param PanoptesProject $panoptesProject
     * @throws \Exception
     */
    public function process(array $data, PanoptesProject $panoptesProject)
    {
        $subject = $this->apiService->getPanoptesSubject($data['subject_ids'][0]);
        $user = $data['user_id'] !== null ? $this->apiService->getPanoptesUser($data['user_id']) : null;

        if ($subject === null) {
            return;
        }

        $this->createDashboardFromPusher($panoptesProject, $data, $subject, $user);
    }

    /**
     * Build item for dashboard.
     * This is built during the posted data from Pusher
     * $this->buildItem($data, $workflow, $subject, $expedition);
     * @param \App\Models\PanoptesProject $panoptesProject
     * @param array $data
     * @param array $subject
     * @param array|null $user
     */
    private function createDashboardFromPusher(PanoptesProject $panoptesProject, array $data, array $subject, array $user = null)
    {

        $thumbnailUri = $this->setPusherThumbnailUri($data);

        $classification_id = (int) $data['classification_id'];

        if ($this->validateTranscription($classification_id)) {
            return;
        }

        $attributes = [
            'classification_id'    => $classification_id,
            'project'              => $panoptesProject->title,
            'description'          => 'Classification Id ' . $data['classification_id'],
            'guid'                 => Uuid::uuid4()->toString(),
            'timestamp'            => Carbon::now(),
            'subject'              => [
                'link'         => isset($subject['metadata']['references']) ? $subject['metadata']['references'] : '',
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

        $this->pusherTranscriptionService->create($attributes);
    }

    /**
     * Determine image url.
     *
     * @param array $data
     * @return mixed
     */
    public function setPusherThumbnailUri(array $data)
    {
        $imageUrl = $data['subject_urls'][0];

        return isset($imageUrl['image/jpeg']) ? $imageUrl['image/jpeg'] : (isset($imageUrl['image/png']) ? $imageUrl['image/png'] : null);
    }

    /**
     * Validate transcription to prevent duplicates.
     *
     * @param $classification_id
     * @return mixed
     */
    public function validateTranscription($classification_id)
    {

        $rules = ['classification_id' => 'unique:mongodb.pusher_transcriptions,classification_id'];
        $values = ['classification_id' => $classification_id];
        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        // returns true if failed.
        return $validator->fails();
    }
}