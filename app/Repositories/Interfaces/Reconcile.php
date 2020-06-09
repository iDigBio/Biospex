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
use Illuminate\Support\Collection;

interface Reconcile extends RepositoryInterface
{
    /**
     * Get reconcile count.
     *
     * @param string $expeditionId
     * @return int
     */
    public function getCount(string $expeditionId): int;

    /**
     * Paginate results.
     *
     * @param array $ids
     * @return mixed
     */
    public function paginate(array $ids);

    /**
     * Get by expedition id.
     *
     * @param string $expeditionId
     * @return \Illuminate\Support\Collection
     */
    public function getByExpeditionId(string $expeditionId): Collection;
}
