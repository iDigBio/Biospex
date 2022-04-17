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

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\WeDigBioDashboard;
use App\Http\Resources\WeDigBioDashboardCollection;
use App\Services\Dashboard\WeDigBioDashboardProcess;

/**
 * Class WeDigBioDashboardController
 *
 * @package App\Http\Controllers\Api\V1
 */
class WeDigBioDashboardController extends ApiController
{
    /**
     * @var \App\Services\Dashboard\WeDigBioDashboardProcess
     */
    private $weDigBioDashboardProcess;

    /**
     * WeDigBioDashboardController constructor.
     *
     * @param \App\Services\Dashboard\WeDigBioDashboardProcess $weDigBioDashboardProcess
     */
    public function __construct(WeDigBioDashboardProcess $weDigBioDashboardProcess)
    {
        $this->weDigBioDashboardProcess = $weDigBioDashboardProcess;
    }

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
     * @return mixed
     */
    public function index()
    {
        if(!request()->user()->tokenCan('wedigbio-dashboard:read')) {
            return $this->errorUnauthorized();
        }

        $request = request()->all();
        $this->weDigBioDashboardProcess->setDashboardQuery($request);
        $numFound = $this->weDigBioDashboardProcess->getTotalCount();
        $limit = $this->weDigBioDashboardProcess->setLimit($request);
        $offset = $this->weDigBioDashboardProcess->setOffset($request);

        $items = $this->weDigBioDashboardProcess->getItems($limit, $offset);

        return (new WeDigBioDashboardCollection($items))->additional([
            'numFound' => $numFound,
            "start"    => $offset,
            "rows"     => $limit,
        ])->collectionRoute('api.v1.wedigbio-dashboard.index')
            ->resourceRoute('api.v1.wedigbio-dashboard.show');
    }

    /**
     * Store resource.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function store()
    {
        return $this->errorForbidden();
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
     * @param string $guid
     * @return mixed
     */
    public function show(string $guid)
    {
        if(!request()->user()->tokenCan('wedigbio-dashboard:read')) {
            return $this->errorUnauthorized();
        }

        $result = $this->weDigBioDashboardProcess->showApiDashboard($guid);

        return (new WeDigBioDashboard($result))->resourceRoute('api.v1.wedigbio-dashboard.show');
    }

    /**
     * Update resource.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function update()
    {
        return $this->errorForbidden();
    }

    /**
     * Destroy resource.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function destroy()
    {
        return $this->errorForbidden();
    }
}