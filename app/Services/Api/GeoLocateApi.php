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

namespace App\Services\Api;

use App\Services\Requests\HttpRequest;

/**
 * Class GeoLocateApi
 */
class GeoLocateApi extends HttpRequest
{
    private string $geolocateStatsUri;

    private string $geolocateDownloadUri;

    /**
     * GeoLocateApi Construct
     */
    public function __construct(protected AwsS3ApiService $awsS3ApiService)
    {
        // Get the geolocate api config values from the config file.
        $this->geolocateStatsUri = config('geolocate.api.geolocate_stats_uri');
        $this->geolocateDownloadUri = config('geolocate.api.geolocate_download_uri');
    }

    /**
     * Get stats from geolocate api.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getStats(string $uri): string
    {
        return $this->getHttpClient()->request('GET', $uri)->getBody()->getContents();
    }

    /**
     * Get download file and save to aws.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDataSourceDownload(string $uri, int $expeditionId, string $type): void
    {
        $filePath = config('geolocate.dir.'.$type).'/'.$expeditionId.'.'.$type;
        $stream = $this->awsS3ApiService->createS3BucketStream(config('filesystems.disks.s3.bucket'), $filePath, 'w', false);
        $opts = [
            'sink' => $stream,
        ];
        $this->getHttpClient()->request('GET', $uri, $opts);
    }

    /**
     * Build stats uri.
     */
    public function buildStatsUri(string $cname, ?string $dname = null): string
    {
        $uri = $this->geolocateStatsUri.'&cname='.$cname;
        $uri .= $dname === null ? '' : '&dname='.$dname;

        return $uri;
    }

    /**
     * Build Downloads uri.
     */
    public function buildDownloadUri(string $cname, string $dname, string $fmt): string
    {
        return $this->geolocateDownloadUri.'&cname='.$cname.'&dname='.$dname.'&fmt='.$fmt;
    }
}
