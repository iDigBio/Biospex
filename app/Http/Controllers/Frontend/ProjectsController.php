<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Project;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\User;
use App\Http\Requests\ProjectFormRequest;
use App\Services\Model\ProjectService;

class ProjectsController extends Controller
{
    /**
     * @var ProjectService
     */
    private $projectService;

    /**
     * ProjectsController constructor.
     *
     * @param ProjectService $projectService
     */
    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Display a listing of the resource.
     * Have to use json_encode + json_decode to fix the different array structure
     * returned by Sentry group queries.
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = \Auth::user();
        $groups = $this->projectService->getUserProjectListByGroup($user);
        $trashed = $this->projectService->getUserProjectListByGroup($user, true);

        if (! $groups->count())
        {
            return redirect()->route('webauth.home.welcome');
        }

        return view('frontend.projects.index', compact('groups', 'trashed'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $vars = $this->projectService->setCommonVariables(request()->user());
        if( ! $vars)
        {
            return redirect()->route('groups.create');
        }

        return view('frontend.projects.create', $vars);
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
        $project = $this->projectService->findWith($projectId, ['group', 'ocrQueue']);
        if ( ! $this->checkPermissions('read', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $user = $userContract->findWith(request()->user()->id, ['profile']);

        $expeditions = $this->projectService->getProjectExpeditions($projectId);
        $trashed = $this->projectService->getProjectExpeditions($projectId, true);

        return view('frontend.projects.show', compact('user', 'project', 'expeditions', 'trashed'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @return mixed
     */
    public function store(ProjectFormRequest $request)
    {
        if ( ! $this->checkPermissions('create', Project::class))
        {
            return redirect()->route('webauth.projects.index');
        }

        $project = $this->projectService->createProject($request->all());

        if ($project)
        {
            return redirect()->route('webauth.projects.show', [$project->id]);
        }

        return redirect()->route('projects.create')->withInput();
    }

    /**
     * Create duplicate project
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function duplicate($projectId)
    {
        $variables = $this->projectService->duplicateProject($projectId);

        if ( ! $variables)
        {
            return redirect()->route('webauth.projects.show', [$projectId]);
        }

        return view('frontend.projects.clone', $variables);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function edit($projectId)
    {
        $variables = $this->projectService->editProject($projectId);

        if ( ! $variables)
        {
            return redirect()->route('webauth.projects.index');
        }

        return view('frontend.projects.edit', $variables);
    }

    /**
     * Update project.
     *
     * @param ProjectFormRequest $request
     * @param Project $project
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(ProjectFormRequest $request, Project $project, $projectId)
    {
        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->projectService->updateProject($request->all(), $projectId);

        return redirect()->route('webauth.projects.show', [$projectId]);
    }

    /**
     * Display project explore page.
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function explore($projectId)
    {
        $project = $this->projectService->findWith($projectId, ['group']);

        if ( ! $this->checkPermissions('read', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $subjectAssignedCount = $this->projectService->explore($projectId);

        return view('frontend.projects.explore', compact('project', 'subjectAssignedCount'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($projectId)
    {
        $project = $this->projectService->findWith($projectId, ['group', 'nfnWorkflows']);

        if ( ! $this->checkPermissions('delete', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->projectService->deleteProject($project);

        return redirect()->route('webauth.projects.index');
    }

    /**
     * Destroy project and related content.
     *
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($projectId)
    {
        $project = $this->projectService->findWith($projectId, ['group', 'expeditions.downloads', 'subjects'], true);

        if ( ! $this->checkPermissions('delete', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->projectService->destroyProject($project);

        return redirect()->route('webauth.projects.index');
    }

    /**
     * Restore project.
     *
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($projectId)
    {
        $project = $this->projectService->findWith($projectId, [], true);

        if ( ! $this->checkPermissions('delete', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->projectService->restoreProject($project);

        return redirect()->route('webauth.projects.show', [$projectId]);
    }

    /**
     * Reprocess OCR.
     *
     * @param $projectId
     * @return mixed
     */
    public function ocr($projectId)
    {
        $project = $this->projectService->findWith($projectId, ['group.permissions']);

        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->projectService->processOcr($project->id);

        return redirect()->route('webauth.projects.show', [$projectId]);
    }
}
