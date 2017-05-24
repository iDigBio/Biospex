<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\BuildOcrBatchesJob;
use App\Repositories\Contracts\OcrQueue;
use App\Repositories\Contracts\Subject;
use App\Repositories\Contracts\User;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use App\Services\Report\NfnProjectCreateReport;
use Illuminate\Http\Request;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Project;
use App\Http\Requests\ProjectFormRequest;
use App\Services\Model\ProjectService;
use Illuminate\Support\Facades\Config;
use JavaScript;

class ProjectsController extends Controller
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var Group
     */
    public $group;

    /**
     * @var Project
     */
    public $project;

    /**
     * ProjectsController constructor.
     *
     * @param Group $group
     * @param Project $project
     * @param Request $request
     */
    public function __construct(
        Group $group,
        Project $project,
        Request $request
    )
    {
        $this->request = $request;
        $this->group = $group;
        $this->project = $project;
    }

    /**
     * Display a listing of the resource.
     * Have to use json_encode + json_decode to fix the different array structure
     * returned by Sentry group queries.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $groups = $this->group->with(['projects'])->whereHas('users', ['id' => $this->request->user()->id])->get();
        $trashed = $this->group->with(['trashedProjects'])->whereHas('users', ['id' => $this->request->user()->id])->get();

        return view('frontend.projects.index', compact('groups', 'trashed'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param ProjectService $service
     * @return \Illuminate\View\View
     */
    public function create(ProjectService $service)
    {
        $vars = $service->setCommonVariables($this->request->user());

        return view('frontend.projects.create', $vars);
    }

    /**
     * Display the specified resource.
     *
     * @param User $userRepo
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show(User $userRepo, $id)
    {
        $user = $userRepo->with(['profile'])->find($this->request->user()->id);

        $with = [
            'group',
            'ocrQueue',
            'expeditions.downloads',
            'expeditions.actors',
            'expeditions.stat'
        ];
        $project = $this->project->with($with)->find($id);

        $expeditions = null;
        $trashed = null;
        foreach ($project->expeditions as $expedition)
        {
            if (null === $expedition->deleted_at)
            {
                $expeditions[] = $expedition;
            }
            else
            {
                $trashed[] = $expedition;
            }
        }

        return view('frontend.projects.show', compact('user', 'project', 'expeditions', 'trashed'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @param NfnProjectCreateReport $report
     * @return mixed
     */
    public function store(ProjectFormRequest $request, NfnProjectCreateReport $report)
    {
        $group = $this->group->with(['permissions'])->find($request->get('group_id'));

        if ( ! $this->checkPermissions($this->request->user(), [\App\Models\Project::class, $group], 'create'))
        {
            return redirect()->route('web.projects.index');
        }

        $project = $this->project->create($request->all());

        if ($project)
        {
            $report->complete($project->id);

            session_flash_push('success', trans('projects.project_created'));
            return redirect()->route('web.projects.show', [$project->id]);
        }

        session_flash_push('error', trans('projects.project_save_error'));

        return redirect()->route('projects.create')->withInput();
    }

    /**
     * Create duplicate project
     *
     * @param ProjectService $service
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function duplicate(ProjectService $service, $id)
    {
        $project = $this->project->with(['group', 'expeditions.workflowManager'])->find($id);

        if ( ! $project)
        {
            session_flash_push('error', trans('pages.project_repo_error'));

            return redirect()->route('web.projects.show', [$id]);
        }

        $common = $service->setCommonVariables($this->request->user());
        $variables = array_merge($common, ['project' => $project, 'workflowCheck' => '']);

        return view('frontend.projects.clone', $variables);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param ProjectService $service
     * @param $id
     * @return \Illuminate\View\View
     */
    public function edit(ProjectService $service, $id)
    {
        $project = $this->project->with(['group.permissions', 'nfnWorkflows'])->find($id);

        if ( ! $this->checkPermissions($this->request->user(), [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $workflowEmpty = ! isset($project->nfnWorkflows) || $project->nfnWorkflows->isEmpty();
        $common = $service->setCommonVariables($this->request->user());

        $variables = array_merge($common, ['project' => $project, 'workflowEmpty' => $workflowEmpty]);

        return view('frontend.projects.edit', $variables);
    }

    /**
     * Update project.
     *
     * @param ProjectFormRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(ProjectFormRequest $request)
    {
        $project = $this->project->find($request->input('id'));

        if ( ! $this->checkPermissions($this->request->user(), [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $this->project->update($request->all(), $project->id);

        session_flash_push('success', trans('projects.project_updated'));

        return redirect()->route('web.projects.show', [$project->id]);
    }

    /**
     * Display project explore page.
     *
     * @param Subject $subject
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function explore(Subject $subject, $id)
    {
        $project = $this->project->with(['group'])->find($id);

        if ( ! $this->checkPermissions($this->request->user(), [$project], 'read'))
        {
            return redirect()->route('web.projects.index');
        }

        $subjectAssignedCount = $subject->where(['project_id' => (int) $id])
            ->whereRaw(['expedition_ids.0' => ['$exists' => true]])
            ->count();

        JavaScript::put([
            'projectId'    => $project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => Config::get('config.expedition_size'),
            'url'          => route('web.grids.explore', [$project->id]),
            'exportUrl'    => route('web.grids.project.export', [$project->id]),
            'showCheckbox' => true,
            'explore'      => true
        ]);

        return view('frontend.projects.explore', compact('project', 'subjectAssignedCount'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ModelDeleteService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(ModelDeleteService $service, $id)
    {
        $project = $this->project->with(['group'])->find($id);

        if ( ! $this->checkPermissions($this->request->user(), [$project], 'delete'))
        {
            return redirect()->route('web.projects.index');
        }

        $result = $service->deleteProject($project->id);

        $result ? session_flash_push('success', trans('projects.project_deleted')) :
            session_flash_push('error', trans('projects.project_delete_error'));

        return redirect()->route('web.projects.index');
    }

    /**
     * Destroy project and related content.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ModelDestroyService $service, $id)
    {
        $project = $this->project->with(['group'])->trashed($id);

        if ( ! $this->checkPermissions($this->request->user(), [$project], 'delete'))
        {
            return redirect()->route('web.projects.index');
        }

        $result = $service->destroyProject($project->id);

        $result ? session_flash_push('success', trans('projects.project_destroyed')) :
            session_flash_push('error', trans('projects.project_destroy_error'));

        return redirect()->route('web.projects.index');
    }

    /**
     * Restore project.
     *
     * @param ModelRestoreService $service
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(ModelRestoreService $service, $projectId)
    {
        $service->restoreProject($projectId) ?
            session_flash_push('success', trans('projects.project_restore')) :
            session_flash_push('error', trans('projects.project_restore_error'));

        return redirect()->route('web.projects.show', [$projectId]);
    }

    /**
     * Reprocess OCR.
     *
     * @param OcrQueue $queue
     * @param $projectId
     * @return mixed
     */
    public function ocr(OcrQueue $queue, $projectId)
    {
        $project = $this->project->with(['group.permissions'])->find($projectId);

        if ( ! $this->checkPermissions($this->request->user(), [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $queueCheck = $queue->skipCache()->where(['project_id' => $projectId])->first();

        if ($queueCheck === null)
        {
            $this->dispatch((new BuildOcrBatchesJob($project->id))->onQueue(Config::get('config.beanstalkd.ocr')));

            session_flash_push('success', trans('expeditions.ocr_process_success'));
        }
        else
        {
            session_flash_push('warning', trans('expeditions.ocr_process_error'));
        }

        return redirect()->route('web.projects.show', [$projectId]);
    }
}
