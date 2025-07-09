<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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

use App\Models\PusherClassification;
use App\Services\Api\PanoptesApiService;
use Carbon\Carbon;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Class CreatePusherClassificationService
 */
class CreatePusherClassificationService
{
    /**
     * CreatePusherClassificationService constructor.
     */
    public function __construct(
        protected PanoptesApiService $apiService,
        protected PusherClassification $pusherClassification
    ) {}

    /**
     * Process pusher data for dashboard.
     * Store records temporarily in MySql DB until processed from cron and added to MongoDB.
     *
     * @see \App\Jobs\PusherTranscriptionJob
     *
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

        $this->pusherClassification->updateOrCreate(['classification_id' => $values['classification_id']], ['data' => $values]);
    }

    /**
     * Build item for dashboard.
     * This is built during the posted data from Pusher
     * $this->buildItem($data, $workflow, $subject, $expedition);
     */
    #[ArrayShape(['classification_id' => 'int',
        'project' => 'string',
        'description' => 'string',
        'guid' => 'string',
        'timestamp' => "\Carbon\Carbon",
        'subject' => 'array',
        'contributor' => 'array',
        'transcriptionContent' => 'array',
        'discretionaryState' => 'string',
    ])]
    private function setDashboardData(string $title, array $data, array $subject, ?array $user = null): array
    {

        $thumbnailUri = $this->setPusherThumbnailUri($data);

        return [
            'classification_id' => (int) $data['classification_id'],
            'project' => $title,
            'description' => 'Classification Id '.$data['classification_id'],
            'guid' => \Str::uuid()->toString(),
            'timestamp' => Carbon::now(),
            'subject' => [
                'link' => $subject['metadata']['references'] ?? '',
                'thumbnailUri' => $thumbnailUri,
            ],
            'contributor' => [
                'decimalLatitude' => empty($data['geo']['latitude']) ? 0 : $data['geo']['latitude'],
                'decimalLongitude' => empty($data['geo']['longitude']) ? 0 : $data['geo']['longitude'],
                'ipAddress' => '',
                'transcriber' => $user === null ? '' : $user['login'],
                'physicalLocation' => [
                    'country' => $data['geo']['country_name'],
                    'province' => '',
                    'county' => '',
                    'municipality' => $data['geo']['city_name'],
                    'locality' => '',
                ],
            ],
            'transcriptionContent' => [
                'lat' => '',
                'long' => '',
                'country' => $subject['metadata']['country'] ?? '',
                'province' => $subject['metadata']['stateProvince'] ?? '',
                'county' => $subject['metadata']['county'] ?? '',
                'municipality' => '',
                'locality' => '',
                'date' => '', // which date to use? transcription date is messy
                'collector' => '',
                'taxon' => $subject['metadata']['scientificName'] ?? '',
            ],
            'discretionaryState' => 'Transcribed',
        ];
    }

    /**
     * Determine image url.
     */
    public function setPusherThumbnailUri(array $data): mixed
    {
        $imageUrl = $data['subject_urls'][0];

        return $imageUrl['image/jpeg'] ?? ($imageUrl['image/png'] ?? null);
    }
}
