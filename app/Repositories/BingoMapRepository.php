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

use App\Models\BingoMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class BingoMapRepository
 */
class BingoMapRepository extends BaseRepository
{
    /**
     * BingoMapRepository constructor.
     */
    public function __construct(BingoMap $bingoMap)
    {

        $this->model = $bingoMap;
    }

    /**
     * Get bingo map by id and uuid.
     */
    public function getBingoMapByBingoIdUuid(int $bingoId, string $uuid): ?Model
    {
        return $this->model->where('bingo_id', $bingoId)->where('uuid', $uuid)->first();
    }

    /**
     * Get bingo map for cleaning.
     */
    public function getBingoMapForCleaning(): Collection
    {
        return $this->model->where('created_at', '<', Carbon::now()->subDays(1))->get();
    }
}
