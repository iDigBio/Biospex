<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Model\ProjectService;
use App\Services\Model\StateCountyService;
use App\Services\Process\TranscriptionChartService;
use CountHelper;
use GeneralHelper;
use JavaScript;

/**
 * Class ProjectController
 *
 * @package App\Http\Controllers\Front
 */
class ProjectController extends Controller
{
    /**
     * @var \App\Services\Model\ProjectService
     */
    private $projectService;

    /**
     * ProjectController constructor.
     *
     * @param \App\Services\Model\ProjectService $projectService
     */
    public function __construct(ProjectService $projectService)
    {

        $this->projectService = $projectService;
    }
    /**
     * Public Projects page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $projects = $this->projectService->getPublicProjectIndex();

        return view('front.project.index', compact('projects'));
    }

    /**
     * Public Projects page sort and order.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort()
    {
        if (! request()->ajax()) {
            return null;
        }

        $sort = request()->get('sort');
        $order = request()->get('order');
        $projects = $this->projectService->getPublicProjectIndex($sort, $order);

        return view('front.project.partials.project', compact('projects'));
    }

    /**
     * Show public project page.
     *
     * @param \App\Services\Process\TranscriptionChartService $chartService
     * @param \App\Services\Model\StateCountyService $stateCountyService
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function project(
        TranscriptionChartService $chartService,
        StateCountyService $stateCountyService,
        $slug
    )
    {
        $project = $this->projectService->getProjectPageBySlug($slug);

        [$expeditions, $expeditionsCompleted] = $project->expeditions->partition(function ($expedition) {
            return $expedition->nfnActor->pivot->completed === 0;
        });

        [$events, $eventsCompleted] = $project->events->partition(function ($event) {
            return GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event);
        });

        // TODO change to stat table count
        $transcriptionsCount = CountHelper::projectTranscriptionCount($project->id);
        $transcribersCount = CountHelper::projectTranscriberCount($project->id);

        $years = $chartService->setYearsArray($project->id);

        $states = $stateCountyService->getStateTranscriptCount($project->id);
        $max = abs(round(($states->max('value') + 500), -3));

        JavaScript::put([
            'max'    => $max,
            'states' => $states->toJson(),
            'years' => $years === null ? null : $years->toArray(),
            'project' => $project->id
        ]);

        return view('front.project.home', compact('project', 'years', 'expeditions', 'expeditionsCompleted', 'events', 'eventsCompleted', 'transcriptionsCount', 'transcribersCount'));
    }

    /**
     * State counties for project map.
     *
     * @param $projectId
     * @param $stateId
     * @param \App\Services\Model\StateCountyService $stateCountyService
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function state($projectId, $stateId, stateCountyService $stateCountyService)
    {
        if (! request()->ajax()) {
            return response()->json(['html' => 'Error retrieving the counties.']);
        }

        $counties = $stateCountyService->getCountyTranscriptionCount($projectId, $stateId)->map(function ($item) {
                return [
                    'id'    => str_pad($item->geo_id_2, 5, '0', STR_PAD_LEFT),
                    'value' => $item->transcription_locations_count,
                    'name'  => $item->state_county,
                ];
            });

        return [
            'max'      => abs(round(($counties->max('value') + 500), -3)),
            'counties' => $counties->toJson(),
        ];
    }
}
