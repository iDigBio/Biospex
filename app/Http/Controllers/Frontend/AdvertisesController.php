<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Project;

class AdvertisesController extends Controller
{

    /**
     * @var Project
     */
    private $project;

    /**
     * Advertise constructor.
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Show advertise page.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request, $id)
    {
        $user = $request->user();
        $project = $this->project->with(['group'])->find($id);

        if ( ! $this->checkPermissions($user, [$project], 'read'))
        {
            return redirect()->route('web.projects.index');
        }

        if (empty($project->advertise)) {
            $project = $this->project->update($project->toArray(), $project->id);
        }

        return view('frontend.projects.advertise', compact('project'));
    }

    /**
     * Advertise download.
     * 
     * @param Request $request
     * @param ResponseFactory $response
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function show(Request $request, ResponseFactory $response, $id)
    {
        $user = $request->user();
        $project = $this->project->with(['group'])->find($id);

        if ( ! $this->checkPermissions($user, [$project], 'read'))
        {
            return redirect()->route('web.projects.index');
        }

        return $response->make(json_encode($project->advertise, JSON_UNESCAPED_SLASHES), '200', [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $project->uuid . '.json"'
        ]);
    }

}
