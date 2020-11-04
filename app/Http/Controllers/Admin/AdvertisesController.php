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

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Services\Model\ProjectService;

/**
 * Class AdvertisesController
 *
 * @package App\Http\Controllers\Admin
 */
class AdvertisesController extends Controller
{

    /**
     * Show advertise page.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @param \App\Services\Model\ProjectService $projectService
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function index(ResponseFactory $response, ProjectService $projectService, $projectId)
    {
        $project = $projectService->findWith($projectId, ['group']);

        if ( ! $this->checkPermissions('readProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        return $response->make(json_encode($project->advertise, JSON_UNESCAPED_SLASHES), '200', [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $project->uuid . '.json"'
        ]);
    }
}
