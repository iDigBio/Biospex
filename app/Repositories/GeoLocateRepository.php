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

namespace App\Repositories;

use App\Models\GeoLocateExport;
use Illuminate\Support\LazyCollection;

/**
 * Class GeoLocateRepository
 *
 * @package App\Repositories
 */
class GeoLocateRepository extends BaseRepository
{
    /**
     * GeoLocateForm construct.
     *
     * @param \App\Models\GeoLocateExport $geoLocate
     */
    public function __construct(GeoLocateExport $geoLocate)
    {
        $this->model = $geoLocate;
    }

    /**
     * Get geolocate records by expedition id.
     *
     * @param int $expeditionId
     * @return \Illuminate\Support\LazyCollection
     */
    public function getByExpeditionId(int $expeditionId): LazyCollection
    {
        return $this->model->where('subject_expeditionId', $expeditionId)->options(['allowDiskUse' => true])->timeout(86400)->cursor();
    }
}