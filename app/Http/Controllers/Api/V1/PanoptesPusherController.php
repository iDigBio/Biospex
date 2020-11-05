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

use App\Jobs\PusherEventTranscriptionJob;
use App\Jobs\PusherWeDigBioDashboardJob;
use App\Services\Model\PanoptesProjectService;
use Illuminate\Http\Request;

/**
 * Class PanoptesPusherController
 *
 * @package App\Http\Controllers\Api\V1
 */
class PanoptesPusherController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function index()
    {
        return $this->errorNotFound();
    }

    /**
     * Create classification based on pusher.
     *
     * @param Request $request
     * @param \App\Services\Model\PanoptesProjectService $panoptesProjectService
     * [2017-10-15 00:12:45] lumen.INFO: {"classification_id":"74498341","project_id":"1558","workflow_id":"2838","user_id":null,"subject_ids":["4002829"],"subject_urls":[{"image/jpeg":"https://panoptes-uploads.zooniverse.org/production/subject_location/0640d1cd-6c4f-43b4-9a10-726ebd43fedb.jpeg"}],"geo":{"country_name":"United States","country_code":"US","city_name":"Tallahassee","coordinates":[-84.2539,30.4203],"latitude":30.4203,"longitude":-84.2539}}
     */
    public function create(Request $request, PanoptesProjectService $panoptesProjectService)
    {
        if ( ! $request->isJson()) {
            return;
        }

        $data = json_decode($request->getContent(), true);

        if ( ! isset($data['workflow_id']))
        {
            return;
        }

        $result = $panoptesProjectService->findByProjectIdAndWorkflowId($data['project_id'], $data['workflow_id']);

        if ($result === null){
            return;
        }

        PusherEventTranscriptionJob::dispatch($data);
        PusherWeDigBioDashboardJob::dispatch($data, $result);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function show()
    {
        return $this->errorNotFound();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function update()
    {
        return $this->errorNotFound();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete()
    {
        return $this->errorNotFound();
    }
}
