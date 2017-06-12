<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Contracts\Routing\ResponseFactory;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProjectContract;

class AdvertisesController extends Controller
{

    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * Advertise constructor.
     * @param ProjectContract $projectContract
     */
    public function __construct(ProjectContract $projectContract)
    {
        $this->projectContract = $projectContract;
    }

    /**
     * Show advertise page.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index($id)
    {
        $user = request()->user();
        $project = $this->projectContract->with('group')->find($id);

        if ( ! $this->checkPermissions($user, [$project], 'read'))
        {
            return redirect()->route('web.projects.index');
        }

        if (empty($project->advertise)) {
            $project = $this->projectContract->update($project->id, $project->toArray());
        }

        return view('frontend.projects.advertise', compact('project'));
    }

    /**
     * Advertise download.
     *
     * @param ResponseFactory $response
     * @param $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function show(ResponseFactory $response, $id)
    {
        $user = request()->user();
        $project = $this->projectContract->with('group')->find($id);

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
