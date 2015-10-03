<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectFormRequest;
use App\Services\Common\ProjectService;

class ProjectsController extends Controller
{
    /**
     * @var ProjectService
     */
    private $service;

    /**
     * Instantiate a new ProjectsController.
     *
     * @param ProjectService $service
     */
    public function __construct(ProjectService $service)
    {
        $this->service = $service;
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
        $vars = $this->service->showIndex();

        return view('front.projects.index', $vars);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $vars = $this->service->createForm();

        return view('front.projects.create', $vars);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProjectFormRequest $request
     * @return mixed
     */
    public function store(ProjectFormRequest $request)
    {
        $project = $this->service->store($request);

        if ($project) {
            session_flash_push('success', trans('projects.project_created'));
            return redirect()->route('projects.show', [$project->id]);
        }

        session_flash_push('error', trans('projects.project_save_error'));
        return redirect()->route('projects.create')->withInput();
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show()
    {
        $vars = $this->service->show();
        
        return view('front.projects.show', $vars);
    }

    /**
     * Create duplicate project
     *
     * @return \Illuminate\View\View
     */
    public function duplicate()
    {
        $vars = $this->service->duplicate();

        return view('front.projects.clone', $vars);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $vars = $this->service->edit();

        return view('front.projects.edit', $vars);
    }

    /**
     * Update project.
     *
     * @param ProjectFormRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function update(ProjectFormRequest $request)
    {
        $project = $this->service->update($request);

        session_flash_push('success', trans('projects.project_updated'));

        return redirect()->route('projects.show', [$project->id]);
    }

    /**
     * Show advertise page.
     *
     * @return \Illuminate\View\View
     */
    public function advertise()
    {
        $project = $this->service->advertise();

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
        $project = $this->service->advertiseDownload();

        return response($project->advertise, '200', [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $project->uuid . '.json"'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy()
    {
        if ($this->service->destroy())
            return redirect()->route('projects.index');

        return redirect()->route('projects.index');
    }
}
