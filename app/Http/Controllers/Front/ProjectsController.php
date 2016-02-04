<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
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
     * @var ResponseFactory
     */
    private $response;

    /**
     * ProjectsController constructor.
     * @param ProjectService $service
     * @param User $user
     * @param Group $group
     * @param Project $project
     * @param Request $request
     * @param ResponseFactory $response
     */
    public function __construct(
        ProjectService $service,
        User $user,
        Group $group,
        Project $project,
        Request $request,
        ResponseFactory $response
    )
    {
        $this->service = $service;
        $this->user = $user;
        $this->request = $request;
        $this->group = $group;
        $this->project = $project;
        $this->response = $response;
    }

    /**
     * Display a listing of the resource.
     * Have to use json_encode + json_decode to fix the different array structure
     * returned by Sentry group queries.
     *
     * @return Response
     */
    public function index()
    {
        $user = $this->user->findWith($this->request->user()->id, ['groups.projects']);

        return view('front.projects.index', compact('user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = $this->request->user();
        $vars = $this->service->setCommonVariables($user);

        return view('front.projects.create', $vars);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function read($id)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($id, [
            'group.permissions',
            'expeditions.downloads',
            'expeditions.actors',
            'expeditions.stat'
        ]);

        if ( ! $this->service->checkPermissions($user, [$project], 'read'))
        {
            return redirect()->route('projects.get.index');
        }

        return view('front.projects.read', compact('user', 'project'));
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
        $group = $this->group->findWith($request->get('group_id'), ['permissions']);

        if ( ! $this->service->checkPermissions($user, [\App\Models\Project::class, $group], 'create'))
        {
            return redirect()->route('projects.get.index');
        }

        $project = $this->project->create($request->all());

        if ($project) {
            session_flash_push('success', trans('projects.project_created'));
            return redirect()->route('projects.get.read', [$project->id]);
        }

        session_flash_push('error', trans('projects.project_save_error'));

        return redirect()->route('projects.create')->withInput();
    }

    /**
     * Create duplicate project
     *
     * @return \Illuminate\View\View
     */
    public function duplicate($id)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($id, ['group', 'expeditions.workflowManager']);

        if ( ! $project)
        {
            session_flash_push('error', trans('pages.project_repo_error'));

            return redirect()->route('projects.get.read', [$id]);
        }

        $common = $this->service->setCommonVariables($user);
        $variables = array_merge($common, ['project' => $project, 'workflowCheck' => '']);

        return view('front.projects.clone', $variables);
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
        $project = $this->project->findWith($id, ['group.permissions', 'expeditions.workflowManager']);

        if ( ! $this->service->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $workflowCheck = $this->service->checkWorkflow($project->expeditions);
        $common = $this->service->setCommonVariables($user);

        $variables = array_merge($common, ['project' => $project, 'workflowCheck' => $workflowCheck]);

        return view('front.projects.edit', $variables);
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

        if ( ! $this->service->checkPermissions($user, [$project], 'update'))
        {
            return redirect()->route('projects.get.index');
        }

        $project->advertise = $request->all();
        $project->fill($request->all())->save();

        session_flash_push('success', trans('projects.project_updated'));

        return redirect()->route('projects.get.read', [$project->id]);
    }

    /**
     * Show advertise page.
     *
     * @return \Illuminate\View\View
     */
    public function advertise($id)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($id, ['group']);

        if ( ! $this->service->checkPermissions($user, [$project], 'read'))
        {
            return redirect()->route('projects.get.index');
        }

        if (empty($project->advertise)) {
            $project->advertise = json_decode(json_encode($project), true);
            $project->save();
        }

        return view('front.projects.advertise', compact('project'));
    }

    /**
     * Advertise download
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function advertiseDownload($id)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($id, ['group']);

        if ( ! $this->service->checkPermissions($user, [$project], 'read'))
        {
            return redirect()->route('projects.get.index');
        }

        return $this->response->make(json_encode($project->advertise, JSON_UNESCAPED_SLASHES), '200', [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $project->uuid . '.json"'
        ]);
    }

    /**
     * Display project explore page
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function explore($id)
    {
        $user = $this->request->user();
        $project = $this->project->findWith($id, ['group']);

        if ( ! $this->service->checkPermissions($user, [$project], 'read'))
        {
            return redirect()->route('projects.get.index');
        }

        $subjectAssignedCount = $this->project->getSubjectsAssignedCount($project);

        return view('front.projects.explore', compact('project', 'subjectAssignedCount'));
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
        $project = $this->project->findWith($id, ['group']);

        if ( ! $this->service->checkPermissions($user, [$project], 'delete'))
        {
            return redirect()->route('projects.get.index');
        }

        $this->project->destroy($id);
        session_flash_push('success', trans('projects.project_destroyed'));

        return redirect()->route('projects.get.index');
    }
}
