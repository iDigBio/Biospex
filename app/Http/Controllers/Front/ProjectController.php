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

use App\Facades\CountHelper;
use App\Facades\DateHelper;
use App\Http\Controllers\Controller;
use App\Services\Chart\TranscriptionChartService;
use App\Services\Models\ProjectModelService;
use App\Services\Models\StateCountyModelService;
use JavaScript;

/**
 * Class ProjectController
 */
class ProjectController extends Controller
{
    /**
     * ProjectController constructor.
     */
    public function __construct(private readonly ProjectModelService $projectModelService) {}

    /**
     * Public Projects page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $projects = $this->projectModelService->getPublicProjectIndex();

        return \View::make('front.project.index', compact('projects'));
    }

    /**
     * Public Projects page sort and order.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function sort()
    {
        if (! \Request::ajax()) {
            return null;
        }

        $sort = \Request::get('sort');
        $order = \Request::get('order');
        $projects = $this->projectModelService->getPublicProjectIndex($sort, $order);

        return \View::make('front.project.partials.project', compact('projects'));
    }

    /**
     * Show public project page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function project(
        TranscriptionChartService $chartService,
        StateCountyModelService $stateCountyModelService,
        $slug
    ) {
        $project = $this->projectModelService->getProjectPageBySlug($slug);

        if ($project === null) {

            return \Redirect::route('front.projects.index')->with('danger', t('Unable to locate project. Please alert the Admin.'));
        }

        $expeditions = null;
        $expeditionsCompleted = null;
        if (isset($project->expeditions)) {
            [$expeditions, $expeditionsCompleted] = $project->expeditions->partition(function ($expedition) {
                return $expedition->completed === 0;
            });
        }

        $events = null;
        $eventsCompleted = null;
        if (isset($project->events)) {
            [$events, $eventsCompleted] = $project->events->partition(function ($event) {
                return DateHelper::eventBefore($event) || DateHelper::eventActive($event);
            });
        }

        // TODO change to stat table count
        $transcriptionsCount = CountHelper::projectTranscriptionCount($project->id);
        $transcribersCount = CountHelper::projectTranscriberCount($project->id);

        $years = ! isset($project->amChart) || is_null($project->amChart->data) ?
            null : array_keys($project->amChart->data);

        $states = $stateCountyModelService->getStateTranscriptCount($project->id);
        $max = abs(round(($states->max('value') + 500), -3));

        JavaScript::put([
            'max' => $max,
            'states' => $states->toJson(),
            'years' => $years,
            'project' => $project->id,
        ]);

        return \View::make('front.project.home', compact('project', 'years', 'expeditions', 'expeditionsCompleted', 'events', 'eventsCompleted', 'transcriptionsCount', 'transcribersCount'));
    }
}
