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

namespace App\Http\Controllers\Api\PanoptesTranscription;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ApiController;
use App\Services\Process\PusherTranscriptionProcess;

/**
 * Class PanoptesTranscriptionV1Controller
 *
 * @package App\Http\Controllers\Api\PanoptesTranscription
 */
class PanoptesTranscriptionV1Controller extends ApiController
{
    /**
     * PusherTranscription List.
     *
     * Show JSON representation of PusherTranscription items.
     *
     * @Get("/{?start,rows,date_start,date_end,project_uuid,expedition_uuid}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("start", description="The start of the results to view.", default=0),
     *     @Parameter("rows", description="The amount of rows to return.", default=200),
     *     @Parameter("date_start", description="Return results >= to UTC timestamp."),
     *     @Parameter("date_end", description="Return results <= to UTC timestamp."),
     *     @Parameter("project_uuid", description="Biospex Project ID resource belongs to."),
     *     @Parameter("expedition_uuid", description="Biospex Expedition ID resource belongs to.")
     * })
     *
     * @param Request $request
     * @param PusherTranscriptionProcess $process
     * @return mixed
     */
    public function index(Request $request, PusherTranscriptionProcess $process)
    {
        $totalCount = $process->listApiDashboardCount($request);
        $data = $process->listApiDashboard($request);

        $count = $request->has('rows') ? (int) $request->input('rows') : 200;
        $count = $count > 500 ? 200 : $count;                                              //count
        $current = $request->has('start') ? (int) $request->input('start') : 0; // current

        $next = (int) ($current + $count); // current + count
        $previous = (int) max($current - $count, 0); // current - count
        $this->paginate($current, $previous, $next, $count);

        return $this->respondWithPusherCollection($data, new PusherTranscriptionTransformer(), $totalCount, 'items');
    }

    public function create()
    {

    }

    public function read()
    {

    }

    public function update()
    {

    }

    public function delete()
    {

    }
}