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

namespace App\Repositories\MongoDb;

use App\Models\Reconcile as Model;
use App\Repositories\Interfaces\Reconcile;
use Cache;
use Illuminate\Support\Collection;

class ReconcileRepository extends MongoDbRepository implements Reconcile
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
     * @inheritDoc
     */
    public function getCount(string $expeditionId): int
    {
        $count = Cache::remember(md5(__METHOD__), 14440, function () use($expeditionId) {
            return $this->model->where('subject_expeditionId', $expeditionId)->count();
        });

        return (int) $count;
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $expeditionId)
    {
        return $this->model->with(['transcriptions'])
            ->where('subject_expeditionId', $expeditionId)
            ->where('problem', 1)->orderBy('subject_id', 'asc')
            ->paginate(1);
    }

    /**
     * @inheritDoc
     */
    public function getByExpeditionId(int $expeditionId): Collection
    {
        return $this->model->where('subject_expeditionId', $expeditionId)->get();
    }
}