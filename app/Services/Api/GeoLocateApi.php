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
 * @package App\Services\Api
 */
class GeoLocateApi extends HttpRequest
{
    /**
     * @var string
     */
    private string $geolocateStatsUri;

    /**
     * @var string
     */
    private string $geolocateDownloadUri;

    /**
     * @var \App\Services\Api\AwsS3ApiService
     */
    private AwsS3ApiService $awsS3ApiService;

    /**
     * GeoLocateApi Construct
     */
    public function __construct(AwsS3ApiService $awsS3ApiService)
    {
        // Get the geolocate api config values from the config file.
        $this->geolocateStatsUri = config('geolocate.api.geolocate_stats_uri');
        $this->geolocateDownloadUri = config('geolocate.api.geolocate_download_uri');
        $this->awsS3ApiService = $awsS3ApiService;
    }

    /**
     * Get stats from geolocate api.
     *
     * @param string $uri
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getStats(string $uri): string
    {
        return $this->getHttpClient()->request('GET', $uri)->getBody()->getContents();
    }

    /**
     * Get download file and save to aws.
     *
     * @param string $uri
     * @param int $expeditionId
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDataSourceDownload(string $uri, int $expeditionId): void
    {
       $filePath = config('geolocate.dir.result') . '/' . $expeditionId . '.kml';
       $stream = $this->awsS3ApiService->createS3BucketStream(config('filesystems.disks.s3.bucket'), $filePath, 'w', false);
       $opts = [
           'sink' => $stream
       ];
       $this->getHttpClient()->request('GET', $uri, $opts);
    }

    /**
     * Build stats uri.
     *
     * @param string $cname
     * @param string|null $dname
     * @return string
     */
    public function buildStatsUri(string $cname, string $dname = null): string
    {
        $uri = $this->geolocateStatsUri.'&cname='.$cname;
        $uri .= $dname === null ? '' : '&dname='.$dname;

        return $uri;
    }

    /**
     * Build Downloads uri.
     *
     * @param string $cname
     * @param string $dname
     * @return string
     */
    public function buildDownloadUri(string $cname, string $dname): string
    {
        return $this->geolocateDownloadUri.'&cname='.$cname.'&dname='.$dname;
    }

}