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

namespace App\Services\Api;

use App\Services\Requests\HttpRequest;

/**
 * Class GeoLocateApi
 */
class GeoLocateApi extends HttpRequest
{
    private string $geolocateStatsBaseUrl;

    private string $geolocateDownloadBaseUrl;

    private string $token;

    /**
     * GeoLocateApi Construct
     */
    public function __construct(protected AwsS3ApiService $awsS3ApiService)
    {
        $this->geolocateStatsBaseUrl = config('geolocate.api.base_stats_url');
        $this->geolocateDownloadBaseUrl = config('geolocate.api.base_download_url');
        $this->token = (string) config('geolocate.api.geolocate_token');
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
     * @throws \Exception
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
        $query = [
            'token' => $this->token,
            'cname' => $cname,
        ];

        if ($dname !== null) {
            $query['dname'] = $dname;
        }

        return $this->geolocateStatsBaseUrl.'?'.http_build_query($query);
    }

    /**
     * Build Downloads uri.
     */
    public function buildDownloadUri(string $cname, string $dname, string $fmt): string
    {
        $query = [
            'token' => $this->token,
            'cname' => $cname,
            'dname' => $dname,
            'fmt' => $fmt,
        ];

        return $this->geolocateDownloadBaseUrl.'?'.http_build_query($query);
    }
}
