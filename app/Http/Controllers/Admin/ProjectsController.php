<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DeleteProject;
use App\Jobs\OcrCreateJob;
use App\Repositories\Interfaces\Project;
use App\Http\Requests\ProjectFormRequest;
use App\Services\Model\ProjectService;
use App\Facades\FlashHelper;
use Auth;
use Cache;
use CountHelper;
use Exception;
use JavaScript;

class ProjectsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Services\Model\ProjectService
     */
    private $projectService;

    /**
     * ProjectsController constructor.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Services\Model\ProjectService $projectService
     */
    public function __construct(
        Project $projectContract,
        ProjectService $projectService
    ) {
        $this->projectService = $projectService;
        $this->projectContract = $projectContract;
    }

    /**
     * Show projects list for admin page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        $projects = $this->projectContract->getAdminProjectIndex($user->id);

        return $projects->isEmpty() ? view('admin.welcome') : view('admin.project.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $groupOptions = $this->projectService->userGroupSelectOptions(request()->user());
        $workflowOptions = $this->projectService->workflowSelectOptions();
        $statusOptions = $this->projectService->statusSelectOptions();
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', 1);

        $vars = compact('groupOptions', 'workflowOptions', 'statusOptions', 'resourceOptions', 'resourceCount');

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
        $project = $this->projectContract->getProjectShow($projectId);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        list($expeditionsCompleted, $expeditions) = $project->expeditions->partition(function ($expedition) {
            return $expedition->stat->percent_completed === "100.00";
        });

        return view('admin.project.show', compact('project', 'expeditions', 'expeditionsCompleted'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @return mixed
     */
    public function store(ProjectFormRequest $request)
    {
        $group = $this->projectService->findGroup($request->get('group_id'));

        if (! $this->checkPermissions('createProject', $group)) {
            return redirect()->route('admin.projects.index');
        }

        $model = $this->projectContract->create($request->all());

        if ($model) {
            $project = $this->projectContract->findWith($model->id, ['workflow.actors.contacts']);
            $this->projectService->notifyActorContacts($project);

            FlashHelper::success(__('messages.record_created'));

            return redirect()->route('admin.projects.show', [$project->id]);
        }

        FlashHelper::error(__('messages.record_save_error'));

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
        $project = $this->projectContract->findWith($projectId, ['group', 'expeditions.workflowManager']);

        if (! $project) {
            FlashHelper::error(__('messages.record_get_error'));

            return redirect()->route('admin.projects.show', [$projectId]);
        }

        $groupOptions = $this->projectService->userGroupSelectOptions(request()->user());
        $workflowOptions = $this->projectService->workflowSelectOptions();
        $statusOptions = $this->projectService->statusSelectOptions();
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', 1);

        $vars = compact('project', 'groupOptions', 'workflowOptions', 'statusOptions', 'resourceOptions', 'resourceCount');

        return view('admin.project.clone', $vars);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * $model->relation()->exists(); // bool: true if there is at least one row
     * $model->relation()->count(); // int: number of related rows
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function edit($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group', 'resources']);
        if (! $project) {
            FlashHelper::error(__('messages.record_get_error'));

            return redirect()->route('admin.projects.index');
        }

        $disableWorkflow = $project->nfnWorkflows()->exists() ? 'disabled' : '';

        $groupOptions = $this->projectService->userGroupSelectOptions(request()->user());
        $workflowOptions = $this->projectService->workflowSelectOptions();
        $statusOptions = $this->projectService->statusSelectOptions();
        $resourceOptions = config('config.project_resources');
        $resourceCount = old('entries', $project->resources->count() ?: 1);
        $resources = $project->resources;

        $vars = compact('project', 'resources', 'disableWorkflow', 'groupOptions', 'workflowOptions', 'statusOptions', 'resourceOptions', 'resourceCount');

        return view('admin.project.edit', $vars);
    }

    /**
     * Update project.
     *
     * @param ProjectFormRequest $request
     * @param $projectId
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(ProjectFormRequest $request, $projectId)
    {
        $group = $this->projectService->findGroup($request->get('group_id'));

        if (! $this->checkPermissions('updateProject', $group)) {
            return redirect()->route('admin.projects.index');
        }

        $project = $this->projectContract->update($request->all(), $projectId);

        $project ? FlashHelper::success(__('messages.record_updated')) : FlashHelper::error(__('messages.record_updated_error'));

        return redirect()->back();
    }

    /**
     * Admin Projects page sort and order.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|null
     */
    public function sort()
    {
        if (! request()->ajax()) {
            return null;
        }

        $user = Auth::user();
        $sort = request()->get('sort');
        $order = request()->get('order');
        $projects = $this->projectContract->getAdminProjectIndex($user->id, $sort, $order);

        return view('admin.project.partials.project', compact('projects'));
    }

    /**
     * Display project explore page.
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function explore($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        JavaScript::put([
            'projectId'    => $projectId,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'loadUrl'      => route('admin.grids.load', [$projectId]),
            'gridUrl'      => route('admin.grids.explore', [$projectId]),
            'exportUrl'    => route('admin.grids.export', [$projectId]),
            'editUrl'      => route('admin.grids.delete', [$projectId]),
            'showCheckbox' => true,
            'explore'      => true,
        ]);

        $subjectAssignedCount = CountHelper::getProjectSubjectAssignedCount($projectId);

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
        $project = $this->projectContract->getProjectForDelete($projectId);

        if (! $this->checkPermissions('isOwner', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            if ($project->nfnWorkflows->isNotEmpty() || $project->workflowManagers->isNotEmpty()) {
                FlashHelper::error(__('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));

                redirect()->route('admin.projects.index');
            }

            DeleteProject::dispatch($project);

            FlashHelper::success(__('messages.record_deleted'));

            return redirect()->route('admin.projects.index');
        } catch (Exception $e) {
            FlashHelper::error(__('messages.record_delete_error'));

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
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        OcrCreateJob::dispatch($projectId);

        FlashHelper::success(__('messages.ocr_process_success'));

        return redirect()->route('admin.projects.show', [$projectId]);
    }

    /**
     * Project Stats.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function statistics(Project $projectContract, $projectId)
    {
        $project = $projectContract->findWith($projectId, ['group']);

        $transcribers = CountHelper::getUserTranscriptionCount($projectId)->sortByDesc('transcriptionCount');

        $transcriptions = Cache::tags('panoptes'.$projectId)->remember(md5(__METHOD__.$projectId), 43200, function () use
        (
            $transcribers
        ) {
            return $transcribers->isEmpty() ? null : $transcribers->pluck('transcriptionCount')->pipe(function ($transcribers) {
                return collect(array_count_values($transcribers->sort()->toArray()));
            })->flatMap(function ($users, $count) {
                return [['transcriptions' => $count, 'transcribers' => $users]];
            })->toJson();
        });

        JavaScript::put(['transcriptions' => $transcriptions]);

        return view('admin.project.statistics', compact('project', 'transcribers', 'transcriptions'));
    }
}
