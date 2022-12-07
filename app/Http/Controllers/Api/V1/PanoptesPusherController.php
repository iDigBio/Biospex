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
use App\Jobs\PanoptesPusherJob;
use Illuminate\Http\Response;

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
     * @return \Illuminate\Http\Response
     */
    public function index(): Response
    {
        return $this->errorUnauthorized();
    }

    /**
     * Process Panoptes data from pusher stream.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(): Response
    {
        if (! request()->isJson()) {
            return $this->errorWrongArgs(t('JSON request required'));
        }

        $data = json_decode(request()->getContent(), true);

        if ( ! isset($data['workflow_id'])) {
            return $this->errorWrongArgs(t('Missing workflow_id'));
        }

        PanoptesPusherJob::dispatch($data);

        return $this->respondWithCreated();
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(): Response
    {
        return $this->errorUnauthorized();
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(): Response
    {
        return $this->errorUnauthorized();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(): Response
    {
        return $this->errorUnauthorized();
    }
}