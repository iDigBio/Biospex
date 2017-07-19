<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Jobs\BuildOcrBatchesJob;
use App\Models\Project;
use App\Repositories\Contracts\OcrQueueContract;
use App\Repositories\Contracts\SubjectContract;
use App\Repositories\Contracts\UserContract;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use App\Services\Report\NfnProjectCreateReport;
use App\Repositories\Contracts\GroupContract;
use App\Repositories\Contracts\ProjectContract;
use App\Http\Requests\ProjectFormRequest;
use App\Services\Model\ProjectService;
use JavaScript;

class ProjectsController extends Controller
{

    /**
     * @var GroupContract
     */
    public $groupContract;

    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * ProjectsController constructor.
     *
     * @param GroupContract $groupContract
     * @param ProjectContract $projectContract
     */
    public function __construct(
        GroupContract $groupContract,
        ProjectContract $projectContract
    )
    {
        $this->groupContract = $groupContract;
        $this->projectContract = $projectContract;
    }

    /**
     * Display a listing of the resource.
     * Have to use json_encode + json_decode to fix the different array structure
     * returned by Sentry group queries.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $groups = $this->groupContract->with('projects')
            ->whereHas('users', function ($query)
            {
                $query->where('id', request()->user()->id);
            })
            ->findAll();

        $trashed = $this->groupContract->with('trashedProjects')
            ->whereHas('users', function ($query)
            {
                $query->where('id', request()->user()->id);
            })
            ->findAll();

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
        $vars = $service->setCommonVariables(request()->user());

        return view('frontend.projects.create', $vars);
    }

    /**
     * Display the specified resource.
     *
     * @param UserContract $userContract
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show(UserContract $userContract, $id)
    {
        $user = $userContract->with('profile')->find(request()->user()->id);

        $with = [
            'group',
            'ocrQueue',
            'expeditions.downloads',
            'expeditions.actors',
            'expeditions.stat'
        ];
        $project = $this->projectContract->with($with)->find($id);

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
        $group = $this->groupContract->with('permissions')
            ->find(request()->get('group_id'));

        if ( ! $this->checkPermissions('create', [Project::class, $group]))
        {
            return redirect()->route('web.projects.index');
        }

        $project = $this->projectContract->create($request->all());

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
        $project = $this->projectContract->with(['group', 'expeditions.workflowManager'])->find($id);

        if ( ! $project)
        {
            session_flash_push('error', trans('pages.project_repo_error'));

            return redirect()->route('web.projects.show', [$id]);
        }

        $common = $service->setCommonVariables(request()->user());
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
        $project = $this->projectContract->with(['group.permissions', 'nfnWorkflows'])->find($id);

        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('web.projects.index');
        }

        $workflowEmpty = ! isset($project->nfnWorkflows) || $project->nfnWorkflows->isEmpty();
        $common = $service->setCommonVariables(request()->user());

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
        $project = $this->projectContract->find($request->input('id'));

        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('web.projects.index');
        }

        $this->projectContract->update($project->id, $request->all());

        session_flash_push('success', trans('projects.project_updated'));

        return redirect()->route('web.projects.show', [$project->id]);
    }

    /**
     * Display project explore page.
     *
     * @param SubjectContract $subjectContract
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function explore(SubjectContract $subjectContract, $id)
    {
        $project = $this->projectContract->with('group')->find($id);

        if ( ! $this->checkPermissions('read', $project))
        {
            return redirect()->route('web.projects.index');
        }

        $subjectAssignedCount = $subjectContract->setCacheLifetime(0)
            ->whereRaw(['expedition_ids.0' => ['$exists' => true]])
            ->where('project_id', '=', (int) $id)
            ->count();

        JavaScript::put([
            'projectId'    => $project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
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
        $project = $this->projectContract->with('group')->find($id);

        if ( ! $this->checkPermissions('delete', $project))
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
     * @param ModelDestroyService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ModelDestroyService $service, $id)
    {
        $project = $this->projectContract->with('group')->onlyTrashed($id);

        if ( ! $this->checkPermissions('delete', $project))
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
     * @param OcrQueueContract $ocrQueueContract
     * @param $projectId
     * @return mixed
     */
    public function ocr(OcrQueueContract $ocrQueueContract, $projectId)
    {
        $project = $this->projectContract->with('group.permissions')->find($projectId);

        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('web.projects.index');
        }

        $queueCheck = $ocrQueueContract->setCacheLifetime(0)
            ->where('project_id', '=', $projectId)
            ->findFirst();

        if ($queueCheck === null)
        {
            $this->dispatch((new BuildOcrBatchesJob($project->id))->onQueue(config('config.beanstalkd.ocr')));

            session_flash_push('success', trans('expeditions.ocr_process_success'));
        }
        else
        {
            session_flash_push('warning', trans('expeditions.ocr_process_error'));
        }

        return redirect()->route('web.projects.show', [$projectId]);
    }
}
