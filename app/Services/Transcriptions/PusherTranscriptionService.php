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

/**
 * Service class for handling PusherTranscription operations including CRUD and dashboard queries.
 */
class PusherTranscriptionService
{
    /** @var \Illuminate\Database\Eloquent\Builder Query builder for dashboard operations */
    private \Illuminate\Database\Eloquent\Builder $dashboardQuery;

    /**
     * Create a new PusherTranscriptionService instance.
     *
     * @param  PusherTranscription  $model
     */
    public function __construct(protected PusherTranscription $model) {}

    /**
     * Find a PusherTranscription by column and value.
     *
     * @param  string  $column  Column to search
     * @param  string  $value  Value to match
     * @return \App\Models\PusherTranscription|null
     */
    public function findBy(string $column, string $value): ?PusherTranscription
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * Create a new PusherTranscription record.
     *
     * @param  array  $data
     * @return \App\Models\PusherTranscription
     */
    public function create(array $data): PusherTranscription
    {
        return $this->model->create($data);
    }

    /**
     * Update existing PusherTranscription record.
     *
     * @param  array  $data
     * @param  mixed  $resourceId
     * @return \App\Models\PusherTranscription|false
     */
    public function update(array $data, mixed $resourceId): \App\Models\PusherTranscription|false
    {
        $model = $this->model->find($resourceId);
        $result = $model->fill($data)->save();

        return $result ? $model : false;
    }

    /**
     * Get a total count of dashboard items.
     *
     * @return int
     */
    public function getWeDigBioDashboardCount(): int
    {
        return $this->dashboardQuery->count();
    }

    /**
     * Get paginated dashboard items.
     *
     * @param  int  $limit
     * @param  int  $offset
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getWeDigBioDashboardItems(int $limit, int $offset): \Illuminate\Database\Eloquent\Collection
    {
        return $this->dashboardQuery->limit($limit)->offset($offset)->orderBy('timestamp', 'desc')->get();
    }

    /**
     * Set query builder for dashboard based on request parameters.
     *
     * @param  array  $request
     * @return void
     */
    public function setQueryForDashboard(array $request): void
    {
        $timestampStart = $this->setTimestampStart($request);
        $timestampEnd = $this->setTimestampEnd($request);

        $this->dashboardQuery = $this->model->where(function ($query) use ($timestampStart, $timestampEnd) {
            $query->where('timestamp', '<=', $timestampStart);
            if ($timestampEnd !== null) {
                $query->where('timestamp', '>=', $timestampEnd);
            }
        });
    }

    /**
     * Set start timestamp from request or current time.
     *
     * @param  array  $request
     * @return \Carbon\Carbon
     */
    private function setTimestampStart(array $request): Carbon
    {
        return isset($request['timestampStart']) ? Carbon::parse($request['timestampStart'], 'UTC') : Carbon::now('UTC');
    }

    /**
     * Set the end timestamp from request or null.
     *
     * @param  array  $request
     * @return \Carbon\Carbon|null
     */
    private function setTimestampEnd(array $request): ?Carbon
    {
        return isset($request['timestampEnd']) ? Carbon::parse($request['timestampEnd'], 'UTC') : null;
    }
}
