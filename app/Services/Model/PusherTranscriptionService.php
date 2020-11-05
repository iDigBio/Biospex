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
use App\Services\Model\Traits\ModelTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class PusherTranscriptionService
 *
 * @package App\Services\Model
 */
class PusherTranscriptionService extends BaseModelService
{
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

        return $this->model->where(function ($query) use ($request) {
            $this->buildQuery($query, $request);
        })->limit($count)->offset($current)->orderBy('timestamp', 'desc')->get();
    }

    /**
     * Build query.
     *
     * @param $query
     * @param $request
     */
    private function buildQuery(&$query, $request)
    {
        $request->has('project_uuid') ? $query->where('projectUuid', $request->input('project_uuid')) : false;
        $request->has('expedition_uuid') ? $query->where('expeditionUuid', $request->input('expedition_uuid')) : false;

        $date_start = is_numeric($request->input('date_start')) ? (int) $request->input('date_start') : $request->input('date_start');
        $date_end = is_numeric($request->input('date_end')) ? (int) $request->input('date_end') : $request->input('date_end');

        if ($date_start !== null && $date_end !== null)
        {
            $timestamps = [
                Carbon::parse($date_start),
                Carbon::parse($date_end)
            ];
            $query->whereBetween('timestamp', $timestamps);

            return;
        }

        $date_start !== null ? $query->where('timestamp', '>=', $date_start) : null;
        $date_end !== null ? $query->where('timestamp', '<=', $date_end) : null;
    }
}