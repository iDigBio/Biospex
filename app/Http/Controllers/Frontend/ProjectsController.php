<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Project;

class ProjectsController extends Controller
{
    /**
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Project $projectContract)
    {
        $projects = $projectContract->getPublicIndex();
        return view('front.project.index');
    }

    /**
     * Show public project page.
     *
     * @param $slug
     * @param Project $projectContract
     * @return \Illuminate\View\View
     */
    public function project($slug, Project $projectContract)
    {
        $project = $projectContract->getProjectPageBySlug($slug);
        $events = $project->events->sortByDesc('start_date');

        return view('frontend.project', compact('project', 'events'));
    }
}
