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

use App\Models\Expedition;
use App\Models\GeoLocateCommunity;
use App\Models\GeoLocateDataSource;
use App\Services\Api\GeoLocateApi;

/**
 * Class GeoLocateStatService
 */
class GeoLocateStatService
{
    /**
     * GeoLocateStatService constructor.
     */
    public function __construct(
        private GeoLocateCommunity $geoLocateCommunity,
        private GeoLocateDataSource $geoLocateDataSource,
        private GeoLocateApi $geoLocateApi
    ) {}

    /**
     * Save community and data source.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function saveCommunityDataSource(array $data, Expedition &$expedition): void
    {
        if (! empty($data['community'])) {
            $this->getCommunityDataSource($data['community']);
            $community = $this->updateOrCreateCommunity($expedition->project_id, $data['community']);
        } else {
            $community = $this->geoLocateCommunity->find($data['community_id']);
        }

        if (! empty($data['data_source'])) {
            $this->getCommunityDataSource($community->name, $data['data_source']);
        }

        $this->updateOrCreateDataSource($expedition, $community->id, $data['data_source']);

        $expedition->actors()->updateExistingPivot(config('geolocate.actor_id'), [
            'state' => 2,
        ]);
    }

    /**
     * Create or update community.
     */
    public function updateOrCreateCommunity(int $projectId, string $community): \App\Models\GeoLocateCommunity
    {
        $attributes = [
            'project_id' => $projectId,
            'name' => $community,
        ];
        $values = [
            'project_id' => $projectId,
            'name' => $community,
        ];

        return $this->geoLocateCommunity->updateOrCreate($attributes, $values);
    }

    /**
     * Update or Create GeoLocateDataSource.
     */
    public function updateOrCreateDataSource(Expedition $expedition, int $communityId, string $dataSource): \App\Models\GeoLocateDataSource
    {
        $attributes = [
            'project_id' => $expedition->project_id,
            'expedition_id' => $expedition->id,
        ];
        $values = [
            'project_id' => $expedition->project_id,
            'expedition_id' => $expedition->id,
            'geo_locate_community_id' => $communityId,
            'data_source' => $dataSource,
        ];

        return $this->geoLocateDataSource->updateOrCreate($attributes, $values);
    }

    /**
     * Update community stat.
     */
    public function updateGeoLocateCommunityStat(int $id, array $data): void
    {

        $this->geoLocateCommunity->updateOrCreate(['id' => $id], ['data' => $data]);
    }

    /**
     * Update data source stat.
     */
    public function updateGeoLocateDataSourceStat(int $id, array $data): void
    {
        $this->geoLocateDataSource->update(['id' => $id], ['data' => $data]);
    }

    /**
     * Get community and data source.
     *
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function getCommunityDataSource(string $cname, ?string $dname = null): array
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
     */
    public function getCommunityAndDataSourceByExpeditionId(int $expeditionId): \App\Models\GeoLocateDataSource
    {
        return $this->geoLocateDataSource->where('expedition_id', $expeditionId)->with(['geoLocateCommunity'])->first();
    }

    /**
     * Build datasource download file.
     */
    public function buildDataSourceDownload(string $cname, string $dname): string
    {
        return $this->geoLocateApi->buildDownloadUri($cname, $dname);
    }

    /**
     * Get DataSource download file.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDataSourceDownload(string $uri, int $expeditionId): void
    {
        $this->geoLocateApi->setHttpProvider();
        $this->geoLocateApi->getDataSourceDownload($uri, $expeditionId);
    }
}
