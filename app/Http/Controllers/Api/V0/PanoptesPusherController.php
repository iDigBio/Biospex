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

namespace App\Http\Controllers\Api\V0;

use App\Jobs\ZooniversePusherHandlerJob;
use Response;

/**
 * Class PanoptesPusherController
 */
class PanoptesPusherController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return $this->errorNotFound();
    }

    /**
     * Create pusher classification.
     */
    public function create(): ?\Illuminate\Http\Response
    {
        if (! \Request::isJson()) {
            return null;
        }

        $data = json_decode(\Request::getContent(), true);

        if (! isset($data['workflow_id'])) {
            return null;
        }

        ZooniversePusherHandlerJob::dispatch($data);

        return $this->respondWithCreated();
    }

    /**
     * Display the specified resource.
     */
    public function show(): Response
    {
        return $this->errorNotFound();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(): Response
    {
        return $this->errorNotFound();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(): Response
    {
        return $this->errorNotFound();
    }
}
