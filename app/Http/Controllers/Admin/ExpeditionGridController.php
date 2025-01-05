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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use App\Models\Project;
use App\Services\Grid\JqGridEncoder;
use Request;
use Response;
use Throwable;

class ExpeditionGridController extends Controller
{
    public function __construct(protected JqGridEncoder $grid) {}

    /**
     * Show grid in expedition create page.
     */
    public function create(Project $project): mixed
    {
        try {
            return $this->grid->encodeGridRequestedData(Request::all(), 'create', $project->id);
        } catch (Throwable $throwable) {
            return Response::make($throwable->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions.
     */
    public function show(Expedition $expedition): mixed
    {
        try {
            return $this->grid->encodeGridRequestedData(Request::all(), 'show', $expedition->project_id, $expedition->id);
        } catch (Throwable $throwable) {
            return Response::make($throwable->getMessage(), 404);
        }
    }

    /**
     * Show grid in expeditions edit.
     */
    public function edit(Expedition $expedition): mixed
    {
        try {
            return $this->grid->encodeGridRequestedData(Request::all(), 'edit', $expedition->project_id, $expedition->id);
        } catch (Throwable $throwable) {
            return Response::make($throwable->getMessage(), 404);
        }
    }
}
