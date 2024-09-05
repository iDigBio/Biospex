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

namespace App\Services\Actor\GeoLocate;

use App\Repositories\GeoLocateCommunityRepository;
use App\Repositories\GeoLocateDataSourceRepository;
use App\Services\Api\GeoLocateApi;

/**
 * Class GeoLocateStat
 *
 * @package App\Services\Process
 */
class GeoLocateStat
{
    /**
     * @var \App\Repositories\GeoLocateCommunityRepository
     */
    private GeoLocateCommunityRepository $geoLocateCommunityRepository;

    /**
     * @var \App\Repositories\GeoLocateDataSourceRepository
     */
    private GeoLocateDataSourceRepository $geoLocateDataSourceRepository;

    /**
     * @var \App\Services\Api\GeoLocateApi
     */
    private GeoLocateApi $geoLocateApi;

    /**
     * GeoLocateStat constructor.
     *
     * @param \App\Repositories\GeoLocateCommunityRepository $geoLocateCommunityRepository
     * @param \App\Repositories\GeoLocateDataSourceRepository $geoLocateDataSourceRepository
     * @param \App\Services\Api\GeoLocateApi $geoLocateApi
     */
    public function __construct(
        GeoLocateCommunityRepository $geoLocateCommunityRepository,
        GeoLocateDataSourceRepository $geoLocateDataSourceRepository,
        GeoLocateApi $geoLocateApi
    ) {
        $this->geoLocateCommunityRepository = $geoLocateCommunityRepository;
        $this->geoLocateDataSourceRepository = $geoLocateDataSourceRepository;
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
            $this->getCommunityDataSource($data['community']);
            $community = $this->updateOrCreateCommunity($projectId, $data['community']);
        } else {
            $community = $this->geoLocateCommunityRepository->find($data['community_id']);
        }

        if (! empty($data['data_source'])) {
            $this->getCommunityDataSource($community->name, $data['data_source']);
        }

        $this->updateOrCreateDataSource($projectId, $expeditionId, $community->id, $data['data_source']);
    }

    /**
     * Create or update community.
     *
     * @param int $projectId
     * @param string $community
     * @return \App\Models\GeoLocateCommunity
     */
    public function updateOrCreateCommunity(int $projectId, string $community): \App\Models\GeoLocateCommunity
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
     * Update or Create GeoLocateDataSource.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @param int $communityId
     * @param string $dataSource
     * @return \App\Models\GeoLocateDataSource
     */
    public function updateOrCreateDataSource(int $projectId, int $expeditionId, int $communityId, string $dataSource): \App\Models\GeoLocateDataSource
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

        return $this->geoLocateDataSourceRepository->updateOrCreate($attributes, $values);
    }

    /**
     * Update community stat.
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateGeoLocateCommunityStat(int $id, array $data): void
    {
        $this->geoLocateCommunityRepository->update(['data' => $data], $id);
    }

    /**
     * Update data source stat.
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateGeoLocateDataSourceStat(int $id, array $data)
    {
        $this->geoLocateDataSourceRepository->update(['data' => $data], $id);
    }

    /**
     * Get community and data source.
     *
     * @param string $cname
     * @param string|null $dname
     * @return array
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function getCommunityDataSource(string $cname, string $dname = null): array
    {
        $uri = $this->geoLocateApi->buildStatsUri($cname, $dname);
        $this->geoLocateApi->setHttpProvider();

        $response = json_decode($this->geoLocateApi->getStats($uri), true);

        if (isset($response['error'])) {
            throw new \Exception($response['error']);
        }

        return $response;
    }

    /**
     * Get community and data source by expedition id.
     *
     * @param int $expeditionId
     * @return \App\Models\GeoLocateDataSource
     */
    public function getCommunityAndDataSourceByExpeditionId(int $expeditionId): \App\Models\GeoLocateDataSource
    {
        return $this->geoLocateDataSourceRepository->findByWith('expedition_id',$expeditionId, ['geoLocateCommunity'])->first();
    }

    /**
     * Build datasource download file.
     *
     * @param string $cname
     * @param string $dname
     * @return string
     */
    public function buildDataSourceDownload(string $cname, string $dname): string
    {
        return $this->geoLocateApi->buildDownloadUri($cname, $dname);
    }

    /**
     * Get DataSource download file.
     *
     * @param string $uri
     * @param int $expeditionId
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDataSourceDownload(string $uri, int $expeditionId): void
    {
        $this->geoLocateApi->setHttpProvider();
        $this->geoLocateApi->getDataSourceDownload($uri, $expeditionId);
    }
}