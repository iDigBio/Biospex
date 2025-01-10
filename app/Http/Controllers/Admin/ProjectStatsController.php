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

use App\Facades\CountHelper;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\Group\GroupService;
use App\Services\JavascriptService;
use App\Services\Project\ProjectService;
use View;

class ProjectStatsController extends Controller
{
    /**
     * ProjectController constructor.
     */
    public function __construct(
        protected ProjectService $projectService,
        protected GroupService $groupService,
        protected JavascriptService $javascriptService
    ) {}

    /**
     * Project Stats.
     */
    public function __invoke(Project $project): \Illuminate\Contracts\View\View
    {
        $project->load('group');

        $transcribers = CountHelper::getTranscribersTranscriptionCount($project->id)->sortByDesc('transcriptionCount');
        $transcriptions = CountHelper::getTranscriptionsPerTranscribers($project->id, $transcribers);

        $this->javascriptService->javascript->put(['transcriptions' => $transcriptions]);

        return View::make('admin.project.statistics', compact('project', 'transcribers', 'transcriptions'));
    }
}
