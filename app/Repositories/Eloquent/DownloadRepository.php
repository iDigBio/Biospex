<?php
/**
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

namespace App\Repositories\Eloquent;

use App\Models\Download as Model;
use App\Repositories\Interfaces\Download;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DownloadRepository extends EloquentRepository implements Download
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getDownloadsForCleaning(): Collection
    {
        return $this->model
            ->where('type', 'export')
            ->where('created_at', '<', Carbon::now()->subDays(90))
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function getExportFiles(string $expeditionId): Collection
    {
        return $this->model
            ->where('expedition_id', $expeditionId)
            ->where('type', 'export')
            ->get();
    }
}