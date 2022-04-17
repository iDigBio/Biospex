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

use App\Models\Reconcile;

/**
 * Class ReconcileRepository
 *
 * @package App\Repositories
 */
class ReconcileRepository extends BaseRepository
{
    /**
     * ReconcileRepository constructor.
     *
     * @param \App\Models\Reconcile $reconcile
     */
    public function __construct(Reconcile $reconcile)
    {

        $this->model = $reconcile;
    }

    /**
     * Get paging for reconcile records.
     * Encoding is used to match columns in reconcile collection.
     *
     * @param int $expeditionId
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paging(int $expeditionId)
    {
        return $this->model->with(['transcriptions'])
            ->where('subject_expeditionId', $expeditionId)
            ->where('subject_problem', 1)
            ->orderBy('subject_id', 'asc')
            ->paginate(1);
    }
}