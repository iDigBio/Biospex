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
use App\Http\Requests\ProjectFormRequest;
use App\Jobs\DeleteProject;
use App\Jobs\DeleteUnassignedSubjectsJob;
use App\Jobs\OcrCreateJob;
use App\Repositories\GroupRepository;
use App\Repositories\ProjectRepository;
use App\Services\Grid\JqGridEncoder;
use Auth;
use CountHelper;
use Exception;
use Flash;
use JavaScript;

/**
 * Class ProjectController
 *
 * @package App\Http\Controllers\Admin
 */
class ProjectController extends Controller
{
    /**
     * @var \App\Repositories\ProjectRepository
     */
    private ProjectRepository $projectRepo;

    /**
     * @var \App\Repositories\GroupRepository
     */
    private GroupRepository $groupRepo;

    /**
     * ProjectController constructor.
     *
     * @param \App\Repositories\ProjectRepository $projectRepo
     * @param \App\Repositories\GroupRepository $groupRepo
     */
    public function __construct(
        ProjectRepository $projectRepo,
        GroupRepository $groupRepo,
    ) {
        $this->projectRepo = $projectRepo;
        $this->groupRepo = $groupRepo;
    }

    /**
     * Show projects list for admin page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        $groups = $this->groupRepo->getUserGroupCount($user->id);
        $projects = $this->projectRepo->getAdminProjectIndex($user->id);

        return $groups === 0 ? view('admin.welcome') : view('admin.project.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $groupOptions = ['' => '--Select--'] + $this->groupRepo->getUsersGroupsSelect(request()->user());
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', 1);

        $vars = compact('groupOptions', 'resourceOptions', 'resourceCount');

        return view('admin.project.create', $vars);
    }

    /**
     * Display the specified resource.
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show($projectId)
    {
        $project = $this->projectRepo->getProjectShow($projectId);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        [$expeditions, $expeditionsCompleted] = $project->expeditions->partition(function ($expedition) {
            return $expedition->completed === 0;
        });

        $transcriptionsCount = CountHelper::projectTranscriptionCount($project->id);
        $transcribersCount = CountHelper::projectTranscriberCount($project->id);

        $viewParams = [
            'project'              => $project,
            'group'                => $project->group,
            'expeditions'          => $expeditions,
            'expeditionsCompleted' => $expeditionsCompleted,
            'transcriptionsCount'  => $transcriptionsCount,
            'transcribersCount'    => $transcribersCount,
        ];

        return view('admin.project.show', $viewParams);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @return mixed
     */
    public function store(ProjectFormRequest $request)
    {
        $group = $this->groupRepo->find($request->get('group_id'));

        if (! $this->checkPermissions('createProject', $group)) {
            return redirect()->route('admin.projects.index');
        }

        $model = $this->projectRepo->create($request->all());

        if ($model) {
            Flash::success(t('Record was created successfully.'));

            return redirect()->route('admin.projects.show', [$model->id]);
        }

        Flash::error(t('An error occurred when saving record.'));

        return redirect()->route('admin.projects.create')->withInput();
    }

    /**
     * Create duplicate project
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function clone($projectId)
    {
        $project = $this->projectRepo->findWith($projectId, ['group']);

        if (! $project) {
            Flash::error(t('Error retrieving record from database'));

            return redirect()->route('admin.projects.show', [$projectId]);
        }

        $groupOptions = ['' => '--Select--'] + $this->groupRepo->getUsersGroupsSelect(request()->user());
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', 1);

        $vars = compact('project', 'groupOptions', 'resourceOptions', 'resourceCount');

        return view('admin.project.clone', $vars);
    }

    /**
     * Edit project.
     *
     * $model->relation()->exists(); // bool: true if there is at least one row
     * $model->relation()->count(); // int: number of related rows
     *
     * @param $projectId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($projectId)
    {
        $project = $this->projectRepo->findWith($projectId, ['group', 'resources']);
        if (! $project) {
            Flash::error(t('Error retrieving record from database'));

            return redirect()->route('admin.projects.index');
        }

        $groupOptions = ['' => '--Select--'] + $this->groupRepo->getUsersGroupsSelect(request()->user());
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', $project->resources->count() ?: 1);
        $resources = $project->resources;

        $vars = compact('project', 'resources', 'groupOptions', 'resourceOptions', 'resourceCount');

        return view('admin.project.edit', $vars);
    }

    /**
     * Update project.
     *
     * @param ProjectFormRequest $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProjectFormRequest $request, $projectId)
    {
        $group = $this->groupRepo->find($request->get('group_id'));

        if (! $this->checkPermissions('updateProject', $group)) {
            return redirect()->route('admin.projects.index');
        }

        $project = $this->projectRepo->update($request->all(), $projectId);

        $project ? Flash::success(t('Record was updated successfully.')) : Flash::error(t('Error while updating record.'));

        return redirect()->back();
    }

    /**
     * Admin Projects page sort and order.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function sort()
    {
        if (! request()->ajax()) {
            return null;
        }

        $user = Auth::user();
        $sort = request()->get('sort');
        $order = request()->get('order');
        $projects = $this->projectRepo->getAdminProjectIndex($user->id, $sort, $order);

        return view('admin.project.partials.project', compact('projects'));
    }

    /**
     * Display project explore page.
     *
     * @param $projectId
     * @param \App\Services\Grid\JqGridEncoder $grid
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function explore($projectId, JqGridEncoder $grid)
    {
        $project = $this->projectRepo->findWith($projectId, ['group']);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $model = $grid->loadGridModel($projectId);

        JavaScript::put([
            'model'      => $model,
            'subjectIds' => [],
            'maxCount'   => config('config.expedition_size'),
            'dataUrl'    => route('admin.grids.explore', [$projectId]),
            'exportUrl'  => route('admin.grids.export', [$projectId]),
            'checkbox'   => false,
            'route'      => 'explore', // used for export
        ]);

        $subjectAssignedCount = $this->projectRepo->find($projectId)->expeditionStats->sum('local_subject_count');

        return view('admin.project.explore', compact('project', 'subjectAssignedCount'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($projectId)
    {
        $project = $this->projectRepo->getProjectForDelete($projectId);

        if (! $this->checkPermissions('isOwner', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            if ($project->panoptesProjects->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {
                Flash::error(t('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));

                redirect()->route('admin.projects.index');
            }

            DeleteProject::dispatch($project);

            Flash::success(t('Record has been scheduled for deletion and changes will take effect in a few minutes.'));

            return redirect()->route('admin.projects.index');
        } catch (Exception $e) {
            Flash::error(t('An error occurred when deleting record.'));

            return redirect()->route('admin.projects.index');
        }
    }

    /**
     * Reprocess OCR.
     *
     * @param $projectId
     * @return mixed
     */
    public function ocr($projectId)
    {
        $project = $this->projectRepo->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        OcrCreateJob::dispatch($projectId);

        Flash::success(t('OCR processing has been submitted. It may take some time before appearing in the Processes menu. You will be notified by email when the process is complete.'));

        return redirect()->route('admin.projects.show', [$projectId]);
    }

    /**
     * Project Stats.
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function statistics($projectId)
    {
        $project = $this->projectRepo->findWith($projectId, ['group']);

        $transcribers = CountHelper::getTranscribersTranscriptionCount($projectId)->sortByDesc('transcriptionCount');
        $transcriptions = CountHelper::getTranscriptionsPerTranscribers($projectId, $transcribers);

        JavaScript::put(['transcriptions' => $transcriptions]);

        return view('admin.project.statistics', compact('project', 'transcribers', 'transcriptions'));
    }

    /**
     * Delete all unassigned subjects for project.
     *
     * @param string $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteSubjects(string $projectId)
    {
        try {
            DeleteUnassignedSubjectsJob::dispatch(Auth::user(), (int) $projectId);

            Flash::success(t('Subjects have been set for deletion. You will be notified by email when complete.'));

            return redirect()->route('admin.projects.explore', [$projectId]);
        } catch (Exception $e) {
            Flash::warning(t('There was an error setting the job to delete the Subjects.'));

            return redirect()->route('admin.projects.explore', [$projectId]);
        }
    }
}
