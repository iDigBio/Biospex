<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Subject;
use Illuminate\Http\Request;
use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Project;
use App\Http\Requests\ProjectFormRequest;
use App\Services\Common\ProjectService;

class ProjectsController extends Controller
{
    /**
     * @var ProjectService
     */
    public $service;

    /**
     * @var User
     */
    public $user;

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
     * @param ProjectService $service
     * @param User $user
     * @param Group $group
     * @param Project $project
     * @param Request $request
     */
    public function __construct(
        ProjectService $service,
        User $user,
        Group $group,
        Project $project,
        Request $request
    )
    {
        $this->service = $service;
        $this->user = $user;
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
        $groups = $this->group->with(['projects'])->whereHas('users', ['user_id' => $this->request->user()->id])->get();

        return view('frontend.projects.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $vars = $this->service->setCommonVariables($this->request->user());

        return view('frontend.projects.create', $vars);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = $this->request->user();
        $with = [
            'group.permissions',
            'expeditions.downloads',
            'expeditions.actors',
            'expeditions.stat'
        ];
        $project = $this->project->with($with)->find($id);

        return view('frontend.projects.show', compact('user', 'project'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @return mixed
     */
    public function store(ProjectFormRequest $request)
    {
        $user = $request->user();
        $group = $this->group->with(['permissions'])->find($request->get('group_id'));

        if ( ! $this->checkPermissions($user, [\App\Models\Project::class, $group], 'create'))
        {
            return redirect()->route('web.projects.index');
        }

        $project = $this->project->create($request->all());

        if ($project) {
            session_flash_push('success', trans('projects.project_created'));
            return redirect()->route('web.projects.show', [$project->id]);
        }

        session_flash_push('error', trans('projects.project_save_error'));

        return redirect()->route('projects.create')->withInput();
    }

    /**
     * Create duplicate project
     * 
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function duplicate($id)
    {
        $user = $this->request->user();
        $project = $this->project->with(['group', 'expeditions.workflowManager'])->find($id);

        if ( ! $project)
        {
            session_flash_push('error', trans('pages.project_repo_error'));

            return redirect()->route('web.projects.show', [$id]);
        }

        $common = $this->service->setCommonVariables($user);
        $variables = array_merge($common, ['project' => $project, 'workflowCheck' => '']);

        return view('frontend.projects.clone', $variables);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = $this->request->user();
        $project = $this->project->with(['group.permissions', 'expeditions.workflowManager'])->find($id);

        if ( ! $this->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('web.projects.index');
        }

        $workflowCheck = $this->service->checkWorkflow($project->expeditions);
        $common = $this->service->setCommonVariables($user);

        $variables = array_merge($common, ['project' => $project, 'workflowCheck' => $workflowCheck]);

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
        $user = $request->user();
        $project = $this->project->find($request->input('id'));

        if ( ! $this->checkPermissions($user, [$project], 'update'))
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
        $user = $this->request->user();
        $project = $this->project->with(['group'])->find($id);

        if ( ! $this->checkPermissions($user, [$project], 'read'))
        {
            return redirect()->route('web.projects.index');
        }

        $subjectAssignedCount = $subject->where(['project_id' => (int) $id])
            ->whereRaw(['expedition_ids.0' => ['$exists' => true]])
            ->count();

        return view('frontend.projects.explore', compact('project', 'subjectAssignedCount'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $user = $this->request->user();
        $project = $this->project->with(['group'])->find($id);

        if ( ! $this->checkPermissions($user, [$project], 'delete'))
        {
            return redirect()->route('web.projects.index');
        }

        $this->project->delete($id);
        session_flash_push('success', trans('projects.project_destroyed'));

        return redirect()->route('web.projects.index');
    }
}
