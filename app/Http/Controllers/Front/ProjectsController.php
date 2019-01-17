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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Project $projectContract)
    {
        $projects = $projectContract->getPublicProjectIndex();

        return view('front.project.index', compact('projects'));
    }

    /**
     * Public Projects page sort and order.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort(Project $projectContract)
    {
        if ( ! request()->ajax()) {
            return null;
        }

        $sort = request()->get('sort');
        $order = request()->get('order');
        $projects = $projectContract->getPublicProjectIndex($sort, $order);

        return view('front.project.partials.project', compact('projects'));
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

        list($expeditions, $expeditionsCompleted) = $project->expeditions->partition(function($expedition){
            return $expedition->stat->percent_completed < '100.00';
        });

        list($events, $eventsCompleted) = $project->events->partition(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);
            return $now->between($start_date, $end_date);
        });

        $amChartHeight = GeneralHelper::amChartHeight($project->expeditions->count());
        $amLegendHeight = GeneralHelper::amLegendHeight($project->expeditions->count());

        return view('front.project.home', compact('project', 'expeditions', 'expeditionsCompleted', 'events', 'eventsCompleted', 'amChartHeight', 'amLegendHeight'));
    }
}
