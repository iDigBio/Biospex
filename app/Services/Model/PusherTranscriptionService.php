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

namespace App\Services\Model;

use App\Models\PusherTranscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Class PusherTranscriptionService
 *
 * @package App\Services\Model
 */
class PusherTranscriptionService extends BaseModelService
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    private $dashboardQuery;

    /**
     * PusherTranscriptionService constructor.
     *
     * @param \App\Models\PusherTranscription $pusherTranscription
     */
    public function __construct(PusherTranscription $pusherTranscription)
    {

        $this->model = $pusherTranscription;
    }

    /**
     * Get API WeDigBioDashboard record count.
     *
     * @param \Illuminate\Http\Request $request
     * @param false $count
     * @return \App\Models\PusherTranscription[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection|int
     */
    public function getWeDigBioDashboardApi(Request $request, $count = false)
    {
        if ($count)
        {
            return $this->model->where(function ($query) use ($request) {
                $this->buildQuery($query, $request);
            })->count();
        }

        $count = $request->has('rows') ? (int) $request->input('rows') : 200;
        $count = $count > 500 ? 200 : $count;                                              //count
        $current = $request->has('start') ? (int) $request->input('start') : 0; // current

        $count = $request->has('rows') ? (int) $request->input('rows') : 200;
        $count = $count > 500 ? 200 : $count;                                              //count
        $current = $request->has('start') ? (int) $request->input('start') : 0; // current

        return $this->model->where(function ($query) use ($request) {
            $this->buildQuery($query, $request);
        })->limit($count)->offset($current)->orderBy('timestamp', 'desc')->get();
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
     * @param int $limit
     * @param int $offset
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getWeDigBioDashboardItems(int $limit, int $offset)
    {
        return $this->dashboardQuery->limit($limit)->offset($offset)->orderBy('timestamp', 'desc')->get();
    }

    /**
     * Set query for dashboard.
     *
     * @param array $request
     */
    public function setQueryForDashboard(array $request)
    {
        $timestampStart = $this->setTimestampStart($request);
        $timestampEnd = $this->setTimestampEnd($request);

        $this->dashboardQuery = $this->model->where(function($query) use($timestampStart, $timestampEnd){
            $query->where('timestamp', '<=', $timestampStart);
            isset($date_end) ? $query->where('timestamp', '>=', $timestampEnd) : null;
        });
    }

    /**
     * Set the date_start with default to now.
     *
     * @param array $request
     * @return \Carbon\Carbon
     */
    private function setTimestampStart(array $request): Carbon
    {
        return isset($request['timestampStart']) ? Carbon::parse($request['timestampStart'], 'UTC') : Carbon::now('UTC');
    }

    /**
     * @param array $request
     * @return \Carbon\Carbon|null
     */
    private function setTimestampEnd(array $request): ?Carbon
    {
        return isset($request['timestampEnd']) ? Carbon::parse($request['timestampEnd'], 'UTC') : null;
    }
}