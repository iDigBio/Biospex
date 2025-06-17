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

namespace App\Services\Actor\GeoLocate;

use App\Models\ActorExpedition;
use App\Models\Download;
use App\Services\Api\GeoLocateApi;

/**
 * Class GeoLocateDownloadService
 */
class GeoLocateDownloadService
{
    private string $formatType = '';

    private string $kmlOptions = '';

    private string $csvOptions = '';

    public function __construct(protected GeoLocateApi $geoLocateApi, protected Download $download) {}

    /**
     * Set file type.
     */
    public function setFormatType(string $formatType): void
    {
        $this->formatType = $formatType;
    }

    /**
     * Set uri options for kml
     */
    public function setKmlOptions(string $options): void
    {
        $this->kmlOptions = $options;
    }

    /**
     * Set uri options csv
     */
    public function setCsvOptions(string $options): void
    {
        $this->csvOptions = $options;
    }

    /**
     * Download file.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadFile(int $expeditionId, string $community, string $dataSource): void
    {
        $this->geoLocateApi->setHttpProvider();
        $uri = $this->geoLocateApi->buildDownloadUri($community, $dataSource, $this->formatType);
        $uri .= $this->setFormatOptions();
        $this->geoLocateApi->getDataSourceDownload($uri, $expeditionId, $this->formatType);
    }

    /**
     * Save download.
     */
    public function saveDownload(ActorExpedition $actorExpedition): void
    {
        $this->download->updateOrCreate([
            'expedition_id' => $actorExpedition->expedition_id, 'actor_id' => $actorExpedition->actor_id,
            'file' => $actorExpedition->expedition_id.'.'.$this->formatType, 'type' => $this->formatType,
        ], [
            'expedition_id' => $actorExpedition->expedition_id, 'actor_id' => $actorExpedition->actor_id,
            'file' => $actorExpedition->expedition_id.'.'.$this->formatType, 'type' => $this->formatType,
        ]);
    }

    /**
     * Sets the format options based on the specified format type.
     *
     * @return string The corresponding format options for 'kml' or 'csv'.
     */
    private function setFormatOptions(): string
    {
        return $this->formatType === 'kml' ? $this->kmlOptions : $this->csvOptions;
    }
}
