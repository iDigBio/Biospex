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

use App\Facades\CountHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectFormRequest;
use App\Jobs\DeleteProjectJob;
use App\Jobs\DeleteUnassignedSubjectsJob;
use App\Models\Project;
use App\Services\Grid\JqGridEncoder;
use App\Services\Models\GroupModelService;
use App\Services\Models\ProjectModelService;
use App\Services\Permission\CheckPermission;
use Auth;
use Exception;
use JavaScript;
use Redirect;
use View;

/**
 * Class ProjectController
 */
class ProjectController extends Controller
{
    /**
     * ProjectController constructor.
     */
    public function __construct(
        private readonly ProjectModelService $projectModelService,
        private readonly GroupModelService $groupModelService,
    ) {}

    /**
     * Show projects list for admin page.
     */
    public function index(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();

        $groups = $this->groupModelService->getUserGroupCount($user->id);
        $projects = $this->projectModelService->getAdminProjectIndex($user->id);

        return $groups === 0 ? View::make('admin.welcome') : View::make('admin.project.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\View\View
    {
        $groupOptions = ['' => '--Select--'] + $this->groupModelService->getUsersGroupsSelect(\Request::user());
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', 1);

        $vars = compact('groupOptions', 'resourceOptions', 'resourceCount');

        return View::make('admin.project.create', $vars);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $this->projectModelService->getProjectShow($project);

        if (! CheckPermission::handle('readProject', $project->group)) {
            return Redirect::route('admin.projects.index');
        }

        [$expeditions, $expeditionsCompleted] = $project->expeditions->partition(function ($expedition) {
            return $expedition->completed === 0;
        });

        $transcriptionsCount = CountHelper::projectTranscriptionCount($project->id);
        $transcribersCount = CountHelper::projectTranscriberCount($project->id);

        $viewParams = [
            'project' => $project,
            'group' => $project->group,
            'expeditions' => $expeditions,
            'expeditionsCompleted' => $expeditionsCompleted,
            'transcriptionsCount' => $transcriptionsCount,
            'transcribersCount' => $transcribersCount,
        ];

        return View::make('admin.project.show', $viewParams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectFormRequest $request): mixed
    {
        $group = $this->groupModelService->findWithRelations($request->get('group_id'));

        if (! $this->checkPermissions('createProject', $group)) {
            return Redirect::route('admin.projects.index');
        }

        $model = $this->projectModelService->create($request->all());

        if ($model) {
            return Redirect::route('admin.projects.show', [$model])
                ->with('success', t('Record was created successfully.'));
        }

        return Redirect::route('admin.projects.create')->withInput()->with('danger', t('An error occurred when saving record.'));
    }

    /**
     * Create duplicate project
     */
    public function clone($projectId): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $project = $this->projectModelService->findWithRelations($projectId, ['group']);

        if (! $project) {
            return Redirect::route('admin.projects.show', [$project])
                ->with('danger', t('Error retrieving record from database'));
        }

        $groupOptions = ['' => '--Select--'] + $this->groupModelService->getUsersGroupsSelect(\Request::user());
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', 1);

        $vars = compact('project', 'groupOptions', 'resourceOptions', 'resourceCount');

        return View::make('admin.project.clone', $vars);
    }

    /**
     * Edit project.
     *
     * $model->relation()->exists(); // bool: true if there is at least one row
     * $model->relation()->count(); // int: number of related rows
     */
    public function edit($projectId): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $project = $this->groupModelService->findWithRelations($projectId, ['group', 'resources']);
        if (! $project) {

            return Redirect::route('admin.projects.index')
                ->with('danger', t('Error retrieving record from database'));
        }

        $groupOptions = ['' => '--Select--'] + $this->groupModelService->getUsersGroupsSelect(\Request::user());
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', $project->resources->count() ?: 1);
        $resources = $project->resources;

        $vars = compact('project', 'resources', 'groupOptions', 'resourceOptions', 'resourceCount');

        return View::make('admin.project.edit', $vars);
    }

    /**
     * Update project.
     */
    public function update(ProjectFormRequest $request, $projectId): \Illuminate\Http\RedirectResponse
    {
        $group = $this->groupModelService->findWithRelations($request->get('group_id'));

        if (! $this->checkPermissions('updateProject', $group)) {
            return Redirect::route('admin.projects.index');
        }

        $project = $this->projectModelService->update($request->all(), $projectId);

        return $project ?
            Redirect::back()->with('success', t('Record was updated successfully.')) :
            Redirect::back()->with('danger', t('Error while updating record.'));
    }

    /**
     * Admin Projects page sort and order.
     */
    public function sort(): ?\Illuminate\Contracts\View\View
    {
        if (! \Request::ajax()) {
            return null;
        }

        $user = Auth::user();
        $sort = \Request::get('sort');
        $order = \Request::get('order');
        $projects = $this->projectModelService->getAdminProjectIndex($user->id, $sort, $order);

        return View::make('admin.project.partials.project', compact('projects'));
    }

    /**
     * Display project explore page.
     */
    public function explore($projectId, JqGridEncoder $grid): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        $project = $this->projectModelService->findWithRelations($projectId, ['group']);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return Redirect::route('admin.projects.index');
        }

        $model = $grid->loadGridModel($projectId);

        JavaScript::put([
            'model' => $model,
            'subjectIds' => [],
            'maxCount' => config('config.expedition_size'),
            'dataUrl' => route('admin.grids.explore', [$projectId]),
            'exportUrl' => route('admin.grids.export', [$projectId]),
            'checkbox' => false,
            'route' => 'explore', // used for export
        ]);

        $subjectAssignedCount = $this->projectModelService->findWithRelations($projectId)->expeditionStats->sum('local_subject_count');

        return View::make('admin.project.explore', compact('project', 'subjectAssignedCount'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete($projectId): \Illuminate\Http\RedirectResponse
    {
        $project = $this->projectModelService->getProjectForDelete($projectId);

        if (! $this->checkPermissions('isOwner', $project->group)) {
            return Redirect::route('admin.projects.index');
        }

        try {
            if ($project->panoptesProjects->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {

                Redirect::route('admin.projects.index')
                    ->with('danger', t('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));
            }

            DeleteProjectJob::dispatch($project);

            return Redirect::route('admin.projects.index')
                ->with('success', t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));
        } catch (Exception $e) {

            return Redirect::route('admin.projects.index')->with('danger', t('An error occurred when deleting record.'));
        }
    }

    /**
     * Project Stats.
     */
    public function statistics($projectId): \Illuminate\Contracts\View\View
    {
        $project = $this->projectModelService->findWithRelations($projectId, ['group']);

        $transcribers = CountHelper::getTranscribersTranscriptionCount($projectId)->sortByDesc('transcriptionCount');
        $transcriptions = CountHelper::getTranscriptionsPerTranscribers($projectId, $transcribers);

        JavaScript::put(['transcriptions' => $transcriptions]);

        return View::make('admin.project.statistics', compact('project', 'transcribers', 'transcriptions'));
    }

    /**
     * Delete all unassigned subjects for project.
     */
    public function deleteSubjects(int $projectId): \Illuminate\Http\RedirectResponse
    {
        try {
            DeleteUnassignedSubjectsJob::dispatch(Auth::user(), (int) $projectId);

            return Redirect::route('admin.projects.explore', [$projectId])
                ->with('success', t('Subjects have been set for deletion. You will be notified by email when complete.'));
        } catch (Exception $e) {

            return Redirect::route('admin.projects.explore', [$projectId])
                ->with('danger', t('An error occurred when deleting Subjects.'));
        }
    }
}
