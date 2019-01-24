<?php

namespace App\Http\Controllers\Admin;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteProject;
use App\Jobs\OcrCreateJob;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\Subject;
use App\Repositories\Interfaces\User;
use App\Http\Requests\ProjectFormRequest;
use App\Services\File\FileService;
use App\Services\Model\CommonVariables;
use JavaScript;

class ProjectsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Group
     */
    private $groupContract;

    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * @var \App\Services\Model\CommonVariables
     */
    private $commonVariables;

    /**
     * ProjectsController constructor.
     *
     * @param \App\Repositories\Interfaces\Group $groupContract
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Services\Model\CommonVariables $commonVariables
     */
    public function __construct(
        Group $groupContract,
        Project $projectContract,
        CommonVariables $commonVariables
    ) {
        $this->groupContract = $groupContract;
        $this->commonVariables = $commonVariables;
        $this->projectContract = $projectContract;
    }

    /**
     * Show projects list for admin page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = \Auth::user();

        $projects = $this->projectContract->getAdminProjectIndex($user->id);

        return $projects->isEmpty() ? view('admin.welcome') : view('admin.project.index', compact('projects'));
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

        $user = \Auth::user();
        $sort = request()->get('sort');
        $order = request()->get('order');
        $projects = $this->projectContract->getAdminProjectIndex($user->id, $sort, $order);

        return view('admin.project.partials.project', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $groups = $this->groupContract->getUsersGroupsSelect(request()->user());
        $vars = $this->commonVariables->setCommonVariables(request()->user(), $groups);
        if ($vars) {
            return view('front.projects.create', $vars);
        }

        return redirect()->route('groups.create');
    }

    /**
     * Display the specified resource.
     *
     * @param User $userContract
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function show(User $userContract, $projectId)
    {
        $project = $this->projectContract->getProjectShow($projectId);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $user = $userContract->findWith(request()->user()->id, ['profile']);

        list($expeditionsCompleted, $expeditions) = $project->expeditions->partition(function ($expedition) {
            return $expedition->stat->percent_completed === "100.00";
        });

        return view('admin.project.show', compact('user', 'project', 'expeditions', 'expeditionsCompleted'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @return mixed
     */
    public function store(ProjectFormRequest $request)
    {
        $group = $this->groupContract->find($request->get('group_id'));

        if (! $this->checkPermissions('createProject', $group)) {
            return redirect()->route('admin.projects.index');
        }

        $project = $this->projectContract->create($request->all());

        if ($project) {
            $this->commonVariables->notifyActorContacts($project->id);

            FlashHelper::success(trans('message.record_created'));

            return redirect()->route('admin.projects.show', [$project->id]);
        }

        FlashHelper::error(trans('messages.record_save_error'));

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
            FlashHelper::error(trans('pages.project_repo_error'));

            return redirect()->route('admin.projects.show', [$projectId]);
        }

        $groups = $this->groupContract->getUsersGroupsSelect(request()->user());
        $common = $this->commonVariables->setCommonVariables(request()->user(), $groups);
        if (! $common) {
            return redirect()->route('admin.projects.show', [$projectId]);
        }

        $variables = array_merge($common, ['project' => $project, 'workflowCheck' => '']);

        return view('front.projects.clone', $variables);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function edit($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group', 'nfnWorkflows', 'resources']);
        if (! $project) {
            FlashHelper::error(trans('pages.project_repo_error'));

            return redirect()->route('admin.projects.index');
        }

        $workflowEmpty = ! isset($project->nfnWorkflows) || $project->nfnWorkflows->isEmpty();

        $groups = $this->groupContract->getUsersGroupsSelect(request()->user());
        $common = $this->commonVariables->setCommonVariables(request()->user(), $groups);
        if (! $common) {
            return redirect()->route('admin.projects.index');
        }

        $variables = array_merge($common, ['project' => $project, 'workflowEmpty' => $workflowEmpty]);

        return view('admin.project.edit', $variables);
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
        $group = $this->groupContract->find($request->get('group_id'));

        if (! $this->checkPermissions('updateProject', $group)) {
            return redirect()->route('admin.projects.index');
        }

        $project = $this->projectContract->update($request->all(), $projectId);

        $project ? FlashHelper::success(trans('messages.record_updated')) : FlashHelper::error(trans('messages.record_updated_error'));

        return redirect()->route('admin.projects.show', [$projectId]);
    }

    /**
     * Display project explore page.
     *
     * @param \App\Repositories\Interfaces\Subject $subjectContract
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function explore(Subject $subjectContract, $projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if (! $this->checkPermissions('readProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        JavaScript::put([
            'projectId' => $projectId,
            'expeditionId' => 0,
            'subjectIds' => [],
            'maxSubjects' => config('config.expedition_size'),
            'gridUrl' => route('admin.grids.explore', [$projectId]),
            'exportUrl' => route('admin.grids.export', [$projectId]),
            'editUrl' => route('admin.grids.delete', [$projectId]),
            'showCheckbox' => true,
            'explore' => true,
        ]);

        $subjectAssignedCount = $subjectContract->getSubjectAssignedCount($projectId);

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
                FlashHelper::error(trans('messages.expedition_process_exists'));

                redirect()->route('admin.projects.index');
            }

            DeleteProject::dispatch($project);

            FlashHelper::success(trans('messages.record_deleted'));

            return redirect()->route('admin.projects.index');
        } catch (\Exception $e) {
            FlashHelper::error(trans('messages.record_delete_error'));

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

        FlashHelper::success(__('OCR processing has been submitted. It may take some time before appearing in the Processes menu. You will be notified by email when the process is complete.'));

        return redirect()->route('admin.projects.show', [$projectId]);
    }

    /**
     * Project Stats.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param \App\Repositories\Interfaces\PanoptesTranscription $transcriptionContract
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function statistics(Project $projectContract, PanoptesTranscription $transcriptionContract, $projectId)
    {
        $project = $projectContract->findWith($projectId, ['group']);

        $transcribers = collect($transcriptionContract->getUserTranscriptionCount($projectId))->sortByDesc('transcriptionCount');

        $transcriptions = \Cache::remember(md5(__METHOD__.$projectId), 240, function () use (
            $transcribers,
            $projectId
        ) {
            $plucked = collect(array_count_values($transcribers->pluck('transcriptionCount')->sort()->toArray()));

            return $plucked->flatMap(function ($users, $count) {
                return [['transcriptions' => $count, 'transcribers' => $users]];
            })->toJson();
        });

        return view('admin.project.statistics', compact('project', 'transcribers', 'transcriptions'));
    }
}
