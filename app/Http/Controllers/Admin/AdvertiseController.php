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

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Permission\CheckPermission;
use Redirect;
use Response;

/**
 * Class AdvertiseController
 */
class AdvertiseController extends Controller
{
    /**
     * Show advertise page.
     */
    public function __invoke(Project $project): \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
    {
        $project->load('group');

        if (! CheckPermission::handle('readProject', $project->group)) {
            return Redirect::route('webauth.projects.index');
        }

        return Response::make(json_encode($project->advertise, JSON_UNESCAPED_SLASHES), '200', [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$project->uuid.'.json"',
        ]);
    }
}
