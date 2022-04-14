<?php
/*
 * Copyright (c) 2022. Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Transcriptions;

use App\Repositories\PusherClassificationRepository;
use App\Services\Api\PanoptesApiService;
use Carbon\Carbon;
use JetBrains\PhpStorm\ArrayShape;
use Ramsey\Uuid\Uuid;

/**
 * Class CreatePusherClassificationService
 *
 * @package App\Services\Transcriptions
 */
class CreatePusherClassificationService
{
    /**
     * @var \App\Services\Api\PanoptesApiService
     */
    private PanoptesApiService $apiService;

    /**
     * @var \App\Repositories\PusherClassificationRepository
     */
    private PusherClassificationRepository $pusherClassificationRepo;

    /**
     * CreatePusherClassificationService constructor.
     *
     * @param \App\Services\Api\PanoptesApiService $apiService
     * @param \App\Repositories\PusherClassificationRepository $pusherClassificationRepo
     */
    public function __construct(
        PanoptesApiService $apiService,
        PusherClassificationRepository $pusherClassificationRepo
    )
    {
        $this->apiService = $apiService;
        $this->pusherClassificationRepo = $pusherClassificationRepo;
    }

    /**
     * Process pusher data for dashboard.
     * Store records temporarily in MySql DB until processed from cron and added to MongoDB.
     *
     * @see \App\Jobs\PusherTranscriptionJob
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
}