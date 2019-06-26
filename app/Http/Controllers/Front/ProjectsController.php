<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\StateCounty;
use App\Repositories\Interfaces\TranscriptionLocation;
use CountHelper;
use GeneralHelper;
use Illuminate\Support\Carbon;
use JavaScript;

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
        if (! request()->ajax()) {
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
     * @param Project $projectContract
     * @param \App\Repositories\Interfaces\StateCounty $stateCountyContract
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function project(Project $projectContract, StateCounty $stateCountyContract, $slug)
    {
        $project = $projectContract->getProjectPageBySlug($slug);

        list($expeditions, $expeditionsCompleted) = $project->expeditions->partition(function ($expedition) {
            return $expedition->stat->percent_completed < '100.00';
        });

        list($events, $eventsCompleted) = $project->events->partition(function ($event) {
            $start_date = $event->start_date->setTimezone($event->timezone);
            $end_date = $event->end_date->setTimezone($event->timezone);
            $now = Carbon::now($event->timezone);

            return $now->between($start_date, $end_date);
        });

        $transcriptionsCount = CountHelper::projectTranscriptionCount($project->id);
        $transcribersCount = CountHelper::projectTranscriberCount($project->id);

        $amChartHeight = GeneralHelper::amChartHeight($project->expeditions->count());
        $amLegendHeight = GeneralHelper::amLegendHeight($project->expeditions->count());

        $results = $stateCountyContract->getStateTranscriptCount($project->id);
        $states = $results->groupBy('state_num')->reject(function ($row, $key) {
            return empty($key);
        })->map(function ($row) {
            $stateAbbr = $row->first()->state_abbr_cap;
            $stateNum = $row->first()->state_num;
            $id = 'US-'.$stateAbbr;
            $count = (int) $row->sum('transcription_locations_count');

            return ['id' => $id, 'value' => $count ?: 0, 'name' => $stateAbbr, 'statenum' => $stateNum];
        })->values(); //->toJson();

        $max = abs(round(($states->max('value') + 500), -3));

        JavaScript::put([
            'max'    => $max,
            'states' => $states->toJson(),
            'series' => $project->amChart === null ?: $project->amChart->series,
            'data'   => $project->amChart === null ?: $project->amChart->data,
        ]);

        return view('front.project.home', compact('project', 'expeditions', 'expeditionsCompleted', 'events', 'eventsCompleted', 'transcriptionsCount', 'transcribersCount', 'amChartHeight', 'amLegendHeight'));
    }

    /**
     * State counties for project map.
     *
     * @param $projectId
     * @param $stateId
     * @param \App\Repositories\Interfaces\TranscriptionLocation $transcriptionLocation
     * @return array
     */
    public function state($projectId, $stateId, TranscriptionLocation $transcriptionLocation)
    {
        if (! request()->ajax()) {
            return response()->json(['html' => 'Error retrieving the counties.']);
        }

        $counties = $transcriptionLocation->getCountyData($projectId, $stateId)->map(function ($item) {
            return ['id'    => str_pad($item->stateCounty->geo_id_2, 5, '0', STR_PAD_LEFT),
                    'value' => $item->count,
                    'name'  => $item->stateCounty->state_county,
            ];
        });

        $dataArray = [
            'max'  => abs(round(($counties->max('value') + 500), -3)),
            'counties' => $counties->toJson(),
        ];

        return $dataArray;
    }
}
