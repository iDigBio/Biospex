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
use App\Models\Expedition;
use App\Notifications\Generic;
use App\Repositories\ExpeditionRepository;
use App\Services\Actor\GeoLocate\GeoLocateStat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

class GeoLocateStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @var \App\Models\Actor $actor
     */
    private Actor $actor;

    /**
     * @var \App\Models\Expedition $expedition
     */
    private Expedition $expedition;

    /**
     * @var bool $refresh
     */
    private bool $refresh;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Actor $actor
     * @param bool $refresh
     */
    public function __construct(Actor $actor, bool $refresh = false)
    {
        $this->actor = $actor;
        $this->refresh = $refresh;
        $this->onQueue(config('config.queue.geolocate'));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(GeoLocateStat $geoLocateStat, ExpeditionRepository $expeditionRepository): void
    {

        $this->expedition = $expeditionRepository->findWith($this->actor->pivot->expedition_id, ['project.group.owner']);
        $geoLocateDataSource = $geoLocateStat->getCommunityAndDataSourceByExpeditionId($this->actor->pivot->expedition_id);

        if (!$this->refresh && $geoLocateDataSource->updated_at->diffInDays(now()) < 2) {
            return;
        }

        // get community stats first
        $communityStats = $geoLocateStat->getCommunityDataSource($geoLocateDataSource->geoLocateCommunity->name);

        // get dataSource stats
        $dataSourceStats = $geoLocateStat->getCommunityDataSource($geoLocateDataSource->geoLocateCommunity->name, $geoLocateDataSource->data_source);

        // update geo_locate_communities data
        $geoLocateStat->updateGeoLocateCommunityStat($geoLocateDataSource->geoLocateCommunity->id, $communityStats);

        // update geo_locate_data_sources data
        $geoLocateStat->updateGeoLocateDataSourceStat($geoLocateDataSource->id, $dataSourceStats);

        // download data source file if completed and notify user
        if ($dataSourceStats['stats']['localityRecords'] === $dataSourceStats['stats']['correctedLocalityRecords']) {
            $uri = $geoLocateStat->buildDataSourceDownload($geoLocateDataSource->geoLocateCommunity->name, $geoLocateDataSource->data_source);
            $geoLocateStat->getDataSourceDownload($uri, $this->actor->pivot->expedition_id);

            $this->actor->pivot->expedition->actors()->updateExistingPivot(config('geolocate.actor_id'), [
                'state' => 3,
            ]);

            $attributes = [
                'subject' => t('GeoLocate stats for %s is complete.', $this->expedition->title),
                'html'    => [
                    t('The GeoLocate Stat process is complete and the KML file is ready for download.'),
                    t('You can download the file from the Downloads button of the Expedition.')
                ]
            ];


            $this->expedition->project->group->owner->notify(new Generic($attributes));
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $throwable
     * @return void
     */
    public function failed(Throwable $throwable): void
    {
        $subject = t('GeoLocate stats for %s failed.', $this->expedition->title);
        $attributes = [
            'subject' => $subject,
            'html'    => [
                t('Error: %s', $subject),
                t('Error: %s', $throwable->getMessage()),
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
            ],
        ];

        $this->expedition->project->group->owner->notify(new Generic($attributes, true));
    }
}
