<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Project;
use GeneralHelper;
use Illuminate\Support\Carbon;

class ProjectsController extends Controller
{
    /**
     * Public Projects page.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param null $sort
     * @param null $order
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Project $projectContract, $sort = null, $order = null)
    {
        $projects = $projectContract->getPublicProjectIndex($sort, $order);

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

        $expeditions = $project->expeditions->filter(function($expedition){
            return $expedition->stat->percent_completed < '100.00';
        });

        $expeditionsCompleted = $project->expeditions->filter(function($expedition){
            return $expedition->stat->percent_completed === '100.00';
        });

        $events = $project->events->filter(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);
            return $now->between($start_date, $end_date);
        });

        $eventsCompleted = $project->events->reject(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);
            return $now->between($start_date, $end_date);
        });

        $amChartHeight = GeneralHelper::amChartHeight($project->expeditions->count());

        return view('front.project.home', compact('project', 'expeditions', 'expeditionsCompleted', 'events', 'eventsCompleted', 'amChartHeight'));
    }
}
