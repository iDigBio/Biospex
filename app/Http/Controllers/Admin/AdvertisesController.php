<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Contracts\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Project;

class AdvertisesController extends Controller
{

    /**
     * Show advertise page.
     *
     * @param \Illuminate\Contracts\Routing\ResponseFactory $response
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function index(ResponseFactory $response, Project $projectContract, $projectId)
    {
        $project = $projectContract->findWith($projectId, ['group']);

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
