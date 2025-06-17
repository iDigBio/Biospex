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

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Class WeDigBioDashboardCollection
 */
class WeDigBioDashboardCollection extends ResourceCollection
{
    /**
     * @var string
     */
    public static $wrap = 'items';

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = 'App\Http\Resources\WeDigBioDashboard';

    /**
     * @var string
     */
    protected $collectionRoute;

    /**
     * @var string
     */
    protected $resourceRoute;

    /**
     * Add for routing.
     *
     * @return $this
     */
    public function collectionRoute($collectionRoute)
    {
        $this->collectionRoute = $collectionRoute;

        return $this;
    }

    /**
     * Add for routing.
     *
     * @return $this
     */
    public function resourceRoute($resourceRoute)
    {
        $this->resourceRoute = $resourceRoute;

        return $this;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->collection->transform(function (WeDigBioDashboard $transcription) {
            return (new WeDigBioDashboard($transcription))->resourceRoute('api.v1.wedigbio-dashboard.show');
        });

        return parent::toArray($request);
    }

    public function with($request)
    {
        return [
            'links' => [
                'self' => route($this->collectionRoute),
            ],
        ];
    }
}
