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
namespace App\Jobs;

use App\Models\Actor;
use App\Services\Actors\GeoLocate\Traits\GeoLocateError;
use App\Services\GeoLocate\StatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GeoLocateStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GeoLocateError;

    /**
     * @var \App\Models\Actor
     */
    private Actor $actor;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Actor $actor
     */
    public function __construct(Actor $actor)
    {
        $this->actor = $actor;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(StatService $statService): void
    {
        $geoLocateDataSource = $statService->getCommunityAndDataSourceByExpeditionId($this->actor->pivot->expedition_id);

        if ($geoLocateDataSource->updated_at->diffInDays(now()) < 2) {
            return;
        }

        // get community stats first
        $communityStats = $statService->getCommunityDataSource($geoLocateDataSource->geoLocateCommunity->name);

        // get dataSource stats
        $dataSourceStats = $statService->getCommunityDataSource($geoLocateDataSource->geoLocateCommunity->name, $geoLocateDataSource->data_source);

        // update geo_locate_communities data
        $statService->updateGeoLocateCommunityStat($geoLocateDataSource->geoLocateCommunity->id, $communityStats);

        // update geo_locate_data_sources data
        $statService->updateGeoLocateDataSourceStat($geoLocateDataSource->id, $dataSourceStats);

        // download data source file if completed and notify user
        if ($this->completed($dataSourceStats)) {
            $uri = $statService->buildDataSourceDownload($geoLocateDataSource->geoLocateCommunity->name, $geoLocateDataSource->data_source);
            $statService->getDataSourceDownload($uri, $this->actor->pivot->expedition_id);

            $this->sendSuccessNotification();
        }
    }

    private function completed($dataSourceStats): bool
    {
        return $dataSourceStats['stats']['localityRecords'] === $dataSourceStats['stats']['correctedLocalityRecords'];
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        $this->sendErrorNotification($exception);
    }
}
