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

use App\Models\Download;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class DownloadRepository
 *
 * @package App\Repositories
 */
class DownloadRepository extends BaseRepository
{

    /**
     * DownloadRepository constructor.
     *
     * @param \App\Models\Download $download
     */
    public function __construct(Download $download)
    {

        $this->model = $download;
    }

    /**
     * Get Zooniverse export files.
     *
     * @param string $expeditionId
     * @return \Illuminate\Support\Collection
     */
    public function getZooniverseExportFiles(string $expeditionId): Collection
    {
        return $this->model
            ->where('actor_id', config('zooniverse.actor_id'))
            ->where('expedition_id', $expeditionId)
            ->where('type', 'export')
            ->get();
    }
}