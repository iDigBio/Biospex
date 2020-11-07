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

namespace App\Http\Controllers\Api\V0;

use App\Services\Process\PusherTranscriptionProcess;
use Illuminate\Http\Request;
use App\Transformers\PusherTranscriptionTransformer;

/**
 * PusherTranscriptions representation.
 *
 * @Resource("PusherTranscription", uri="/PusherTranscription")
 *
 * @package App\Http\Controllers\V1
 */
class PusherTranscriptionsController extends ApiController
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
     * @param PusherTranscriptionProcess $service
     * @return mixed
     */
    public function index(Request $request, PusherTranscriptionProcess $service)
    {
        $totalCount = $service->listApiDashboardCount($request);
        $data = $service->listApiDashboard($request);

        $count = $request->has('rows') ? (int) $request->input('rows') : 200;
        $count = $count > 500 ? 200 : $count;                                              //count
        $current = $request->has('start') ? (int) $request->input('start') : 0; // current

        $next = (int) ($current + $count); // current + count
        $previous = (int) max($current - $count, 0); // current - count
        $this->paginate($current, $previous, $next, $count);

        return $this->respondWithPusherCollection($data, new PusherTranscriptionTransformer(), $totalCount, 'items');
    }

    /**
     * Create a PusherTranscription Item.
     *
     * Show JSON representation of PusherTranscription items.
     *
     * @POST("/")
     * @Versions({"v1"})
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function create()
    {
        return $this->errorNotFound('This feature has not been implemented at this time.');
    }

    /**
     * PusherTranscription List.
     *
     * Show JSON representation of PusherTranscription items.
     *
     * @Get("/{guid}")
     * @Versions({"v1"})
     * @Parameters({
     *     @Parameter("guid", description="GUID of specific resource item", required=true)
     * })
     *
     * @param PusherTranscriptionProcess $service
     * @param $guid
     * @return mixed
     */
    public function show(PusherTranscriptionProcess $service, $guid)
    {
        $result = $service->showApiDashboard($guid);

        return $result === null ?
            $this->errorNotFound() :
            $this->respondWithItem($result, new PusherTranscriptionTransformer(), 'items');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param string $guid
     * @return \Illuminate\Support\Facades\Response
     */
    public function update(Request $request, $guid)
    {
        return $this->errorNotFound('This feature has not been implemented at this time.');
    }

    /**
     * Delete resource.
     *
     * @param $guid
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete($guid)
    {
        return $this->errorNotFound('This feature has not been implemented at this time.');
    }
}
