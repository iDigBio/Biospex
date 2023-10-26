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

use App\Repositories\GeoLocateCommunityRepository;
use App\Repositories\GeoLocateStatsRepository;
use App\Services\Api\GeoLocateApi;

/**
 * Class GeoLocateCommunityService
 *
 * @package App\Services\Process
 */
class GeoLocateCommunityService
{
    /**
     * @var \App\Repositories\GeoLocateCommunityRepository
     */
    private GeoLocateCommunityRepository $geoLocateCommunityRepository;

    /**
     * @var \App\Repositories\GeoLocateStatsRepository
     */
    private GeoLocateStatsRepository $geoLocateStatsRepository;

    /**
     * @var \App\Services\Api\GeoLocateApi
     */
    private GeoLocateApi $geoLocateApi;

    /**
     * GeoLocateCommunityService constructor.
     *
     * @param \App\Repositories\GeoLocateCommunityRepository $geoLocateCommunityRepository
     * @param \App\Repositories\GeoLocateStatsRepository $geoLocateStatsRepository
     * @param \App\Services\Api\GeoLocateApi $geoLocateApi
     */
    public function __construct(
        GeoLocateCommunityRepository $geoLocateCommunityRepository,
        GeoLocateStatsRepository $geoLocateStatsRepository,
        GeoLocateApi $geoLocateApi
    ) {
        $this->geoLocateCommunityRepository = $geoLocateCommunityRepository;
        $this->geoLocateStatsRepository = $geoLocateStatsRepository;
        $this->geoLocateApi = $geoLocateApi;
    }

    /**
     * Save community and data source.
     *
     * @param array $data
     * @param int $projectId
     * @param int $expeditionId
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function saveCommunityDataSource(array $data, int $projectId, int $expeditionId): void
    {
        if (! empty($data['community'])) {
            $this->getCommunityDataSourceExists($data['community']);
            $community = $this->createCommunity($projectId, $data['community']);
        } else {
            $community = $this->geoLocateCommunityRepository->find($data['community_id']);
        }

        if (! empty($data['data_source'])) {
            $this->getCommunityDataSourceExists($community->name, $data['data_source']);
        }

        $this->createStat($projectId, $expeditionId, $community->id, $data['data_source']);
    }

    /**
     * Create or update community.
     *
     * @param int $projectId
     * @param string $community
     * @return \App\Models\GeoLocateCommunity
     */
    public function createCommunity(int $projectId, string $community): \App\Models\GeoLocateCommunity
    {
        $attributes = [
            'project_id' => $projectId,
            'name' => $community
        ];
        $values = [
            'project_id' => $projectId,
            'name' => $community
        ];

        return $this->geoLocateCommunityRepository->updateOrCreate($attributes, $values);
    }

    /**
     * Update or Create GeoLocateStat.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @param int $communityId
     * @param string $dataSource
     * @return \App\Models\GeoLocateStat
     */
    public function createStat(int $projectId, int $expeditionId, int $communityId, string $dataSource): \App\Models\GeoLocateStat
    {
        $attributes = [
            'project_id' => $projectId,
            'expedition_id' => $expeditionId,
        ];
        $values = [
            'project_id' => $projectId,
            'expedition_id' => $expeditionId,
            'geo_locate_community_id' => $communityId,
            'data_source' => $dataSource
        ];

        return $this->geoLocateStatsRepository->updateOrCreate($attributes, $values);
    }

    /**
     * Send request to GeoLocate API.
     *
     * @param string $cname
     * @param string|null $dname
     * @return void
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    private function getCommunityDataSourceExists(string $cname, string $dname = null): void
    {
        $uri = $this->geoLocateApi->buildStatsUri($cname, $dname);
        $this->geoLocateApi->setHttpProvider();

        $response = json_decode($this->geoLocateApi->getStats($uri), true);

        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }
    }
}