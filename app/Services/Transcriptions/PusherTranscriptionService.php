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

namespace App\Services\Transcriptions;

use App\Models\PusherTranscription;
use Carbon\Carbon;

class PusherTranscriptionService
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $dashboardQuery;

    /**
     * PusherTranscriptionService constructor.
     */
    public function __construct(protected PusherTranscription $model) {}

    /**
     * Find by column and value.
     *
     * @return mixed
     */
    public function findBy(string $column, string $value)
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * Create.
     *
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update.
     *
     * @return \App\Models\PusherTranscription|bool
     */
    public function update(array $data, $resourceId): \App\Models\PusherTranscription|false
    {
        $model = $this->model->find($resourceId);
        $result = $model->fill($data)->save();

        return $result ? $model : false;
    }

    /**
     * Return count for current dashboard query.
     *
     * @return mixed
     */
    public function getWeDigBioDashboardCount()
    {
        return $this->dashboardQuery->count();
    }

    /**
     * Get dashboard items.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getWeDigBioDashboardItems(int $limit, int $offset)
    {
        return $this->dashboardQuery->limit($limit)->offset($offset)->orderBy('timestamp', 'desc')->get();
    }

    /**
     * Set query for dashboard.
     */
    public function setQueryForDashboard(array $request)
    {
        $timestampStart = $this->setTimestampStart($request);
        $timestampEnd = $this->setTimestampEnd($request);

        $this->dashboardQuery = $this->model->where(function ($query) use ($timestampStart, $timestampEnd) {
            $query->where('timestamp', '<=', $timestampStart);
            isset($date_end) ? $query->where('timestamp', '>=', $timestampEnd) : null;
        });
    }

    /**
     * Set the date_start with default to now.
     */
    private function setTimestampStart(array $request): Carbon
    {
        return isset($request['timestampStart']) ? Carbon::parse($request['timestampStart'], 'UTC') : Carbon::now('UTC');
    }

    private function setTimestampEnd(array $request): ?Carbon
    {
        return isset($request['timestampEnd']) ? Carbon::parse($request['timestampEnd'], 'UTC') : null;
    }
}
