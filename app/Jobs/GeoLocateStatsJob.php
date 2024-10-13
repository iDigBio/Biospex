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

use App\Models\Expedition;
use App\Notifications\Generic;
use App\Services\Actor\GeoLocate\GeoLocateStatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GeoLocateStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Expedition $expedition, protected bool $refresh = false)
    {
        $this->onQueue(config('config.queue.geolocate'));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(GeoLocateStatService $geoLocateStatService): void
    {
        $this->expedition->load('project.group.owner', 'geoLocateDataSource.geolocateCommunity');
        $geoLocateDataSource = $this->expedition->geoLocateDataSource;
        $geoLocateCommunity = $geoLocateDataSource->geoLocateCommunity;

        if (! $this->refresh && $geoLocateDataSource->updated_at->diffInDays(now()) < 2) {
            return;
        }

        // get community stats
        $communityStats = $geoLocateStatService->getCommunityDataSource($geoLocateCommunity->name);

        // get dataSource stats
        $dataSourceStats = $geoLocateStatService->getCommunityDataSource($geoLocateCommunity->name, $geoLocateDataSource->data_source);

        // update geo_locate_communities data
        $geoLocateStatService->updateGeoLocateCommunityStat($geoLocateCommunity->id, $communityStats);

        // update geo_locate_data_sources data
        $geoLocateStatService->updateGeoLocateDataSourceStat($geoLocateDataSource->id, $dataSourceStats);

        // download data source file if completed and notify user
        if ($dataSourceStats['stats']['localityRecords'] === $dataSourceStats['stats']['correctedLocalityRecords']) {
            $uri = $geoLocateStatService->buildDataSourceDownload($geoLocateCommunity->name, $geoLocateDataSource->data_source);
            $geoLocateStatService->getDataSourceDownload($uri, $this->expedition->id);

            $this->expedition->actors()->updateExistingPivot(config('geolocate.actor_id'), [
                'state' => 3,
            ]);

            $attributes = [
                'subject' => t('GeoLocate stats for %s is complete.', $this->expedition->title),
                'html' => [
                    t('The GeoLocate Stat process is complete and the KML file is ready for download.'),
                    t('You can download the file from the Downloads button of the Expedition.'),
                ],
            ];

            $this->expedition->project->group->owner->notify(new Generic($attributes));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $subject = t('GeoLocate stats for %s failed.', $this->expedition->title);
        $attributes = [
            'subject' => $subject,
            'html' => [
                t('Error: %s', $subject),
                t('Error: %s', $throwable->getMessage()),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
            ],
        ];

        $this->expedition->project->group->owner->notify(new Generic($attributes, true));
    }
}
