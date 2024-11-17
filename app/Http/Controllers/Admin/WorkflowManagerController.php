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

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use App\Services\Expedition\ExpeditionService;
use App\Services\Permission\CheckPermission;
use App\Services\Project\ProjectService;
use App\Services\Workflow\WorkflowManagerService;
use Exception;
use Redirect;
use Throwable;

class WorkflowManagerController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct(
        protected ExpeditionService $expeditionService,
        protected ProjectService $projectService,
        protected WorkflowManagerService $workflowManagerService
    ) {}

    /**
     * Start processing expedition actors
     */
    public function create(Expedition $expedition): \Illuminate\Http\RedirectResponse
    {
        $expedition->load([
            'project.group',
            'zooActorExpedition',
            'panoptesProject',
            'workflowManager',
            'stat',
        ]);

        if (! CheckPermission::handle('updateProject', $expedition->project->group)) {
            return Redirect::route('admin.projects.index');
        }

        try {
            if ($expedition->panoptesProject === null) {
                throw new Exception(t('Zooniverse Workflow Id is missing. Please update the Expedition once Workflow Id is acquired.'));
            }

            $message = $this->workflowManagerService->createProcess($expedition);

            return Redirect::route('admin.expeditions.show', [$expedition])->with('success', $message);
        } catch (Throwable $throwable) {
            return Redirect::route('admin.expeditions.show', [$expedition])
                ->with('danger', t('An error occurred when trying to process the expedition: %s', $throwable->getMessage()));
        }
    }

    /**
     * Stop a expedition process.
     * Does not destroy the process, just stops it.
     */
    public function destroy(Expedition $expedition): \Illuminate\Http\RedirectResponse
    {
        $expedition->load([
            'project.group',
            'workflowManager',
        ]);

        if (! CheckPermission::handle('updateProject', $expedition->project->group)) {
            return Redirect::route('admin.projects.index');
        }

        if (is_null($expedition->workflowManager)) {
            return Redirect::route('admin.expeditions.show', [$expedition])
                ->with('danger', t('Expedition has no processes at this time.'));
        }

        $expedition->workflowManager->stopped = 1;
        $expedition->workflowManager->save();

        return Redirect::route('admin.expeditions.show', [$expedition])
            ->with('success', t('Expedition process has been stopped locally. This does not stop any processing occurring on remote sites.'));
    }
}
