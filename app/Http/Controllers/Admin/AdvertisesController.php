<?php

namespace App\Http\Controllers\Front;

use Illuminate\Contracts\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Project;

class AdvertisesController extends Controller
{

    /**
     * @var Project
     */
    public $projectContract;

    /**
     * Advertise constructor.
     * @param Project $projectContract
     */
    public function __construct(Project $projectContract)
    {
        $this->projectContract = $projectContract;
    }

    /**
     * Show advertise page.
     *
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if ( ! $this->checkPermissions('readProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        if (empty($project->advertise)) {
            $project = $this->projectContract->update($project->toArray(), $project->id);
        }

        return view('front.projects.advertise', compact('project'));
    }

    /**
     * Advertise download.
     *
     * @param ResponseFactory $response
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function show(ResponseFactory $response, $projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        if ( ! $this->checkPermissions('readProject', $project->group))
        {
            return redirect()->route('webauth.projects.index');
        }

        return $response->make(json_encode($project->advertise, JSON_UNESCAPED_SLASHES), '200', [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $project->uuid . '.json"'
        ]);
    }

}
