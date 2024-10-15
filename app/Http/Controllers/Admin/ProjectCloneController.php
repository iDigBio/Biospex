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
use App\Models\Project;
use App\Services\Group\GroupService;
use App\Services\Project\ProjectService;
use Illuminate\Database\Eloquent\Collection;
use Request;
use View;

class ProjectCloneController extends Controller
{
    public function __construct(
        protected ProjectService $projectService,
        protected GroupService $groupService,
        protected Collection $collection
    ) {}

    /**
     * Create duplicate project
     */
    public function __invoke(Project $project): \Illuminate\View\View
    {
        $project->load(['group']);

        $groupOptions = $this->groupService->getUsersGroupsSelect(Request::user());
        $resources = $this->collection->make();

        return View::make('admin.project.clone', compact('project', 'groupOptions', 'resources'));
    }
}
