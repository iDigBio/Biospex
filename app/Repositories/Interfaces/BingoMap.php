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

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BingoMap extends RepositoryInterface
{
    /**
     * Retrieve bingo map locations.
     *
     * @param string $bingoId
     * @return \Illuminate\Support\Collection
     */
    public function getBingoMapsByBingoId(string $bingoId): Collection;

    /**
     * Retrieve bingo map by bingo id and uuid.
     *
     * @param int $bingoId
     * @param string $uuid
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getBingoMapByBingoIdUuid(int $bingoId, string $uuid): ?Model;

    /**
     * Get bingo maps for removal.
     *
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getBingoMapForCleaning(): Collection;
}