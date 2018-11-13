<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Project;

class ProjectsController extends Controller
{
    /**
     * Public Projects page.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param null $sort
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Project $projectContract, $sort = null)
    {
        $projects = $projectContract->getPublicProjectIndex($sort);

        return request()->ajax() ?
            view('front.project.partials.project', compact('projects')) :
            view('front.project.index', compact('projects'));
    }

    /**
     * Show public project page.
     *
     * @param $slug
     * @param Project $projectContract
     * @return \Illuminate\View\View
     */
    public function project(Project $projectContract, $slug)
    {
        $project = $projectContract->getProjectPageBySlug($slug);

        return view('frontend.project', compact('project'));
    }
}
