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

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class WeDigBioDashboard
 */
class WeDigBioDashboard extends JsonResource
{
    /**
     * @var string
     */
    public static $wrap = 'items';

    /**
     * @var string
     */
    protected $resourceRoute;

    /**
     * Add for building route.
     *
     * @return $this
     */
    public function resourceRoute(string $resourceRoute)
    {
        $this->resourceRoute = $resourceRoute;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $contributor = $this->contributor;

        if (isset($contributor['decimalLatitude'])) {
            $contributor['decimalLatitude'] = (float) $contributor['decimalLatitude'];
        }

        if (isset($contributor['decimalLongitude'])) {
            $contributor['decimalLongitude'] = (float) $contributor['decimalLongitude'];
        }

        return [
            'project' => $this->project,
            'description' => $this->description,
            'guid' => $this->guid,
            'timestamp' => $this->timestamp,
            'subject' => $this->subject,
            'contributor' => $contributor,
            'transcriptionContent' => $this->transcriptionContent,
            'discretionaryState' => $this->discretionaryState,
            'links' => [
                'self' => route($this->resourceRoute, ['wedigbio_dashboard' => $this->guid]),
            ],
        ];
    }
}
