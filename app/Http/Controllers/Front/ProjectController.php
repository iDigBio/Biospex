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
use App\Repositories\ProjectRepository;
use App\Repositories\StateCountyRepository;
use App\Services\Chart\TranscriptionChartService;
use CountHelper;
use Flash;
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
     * @var \App\Repositories\ProjectRepository
     */
    private $projectRepo;

    /**
     * ProjectController constructor.
     *
     * @param \App\Repositories\ProjectRepository $projectRepo
     */
    public function __construct(ProjectRepository $projectRepo)
    {

        $this->projectRepo = $projectRepo;
    }

    /**
     * Public Projects page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $projects = $this->projectRepo->getPublicProjectIndex();

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
        $projects = $this->projectRepo->getPublicProjectIndex($sort, $order);

        return view('front.project.partials.project', compact('projects'));
    }

    /**
     * Show public project page.
     *
     * @param \App\Services\Chart\TranscriptionChartService $chartService
     * @param \App\Repositories\StateCountyRepository $stateCountyRepo
     * @param $slug
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function project(
        TranscriptionChartService $chartService,
        StateCountyRepository $stateCountyRepo,
        $slug
    ) {
        $project = $this->projectRepo->getProjectPageBySlug($slug);

        if ($project === null) {
            Flash::error(t('Unable to locate project. Please alert the Admin.'));

            return redirect()->route('front.projects.index');
        }

        $expeditions = null;
        $expeditionsCompleted = null;
        if (isset($project->expeditions)) {
            [$expeditions, $expeditionsCompleted] = $project->expeditions->partition(function ($expedition) {
                return $expedition->nfnActor->pivot->completed === 0;
            });
        }

        $events = null;
        $eventsCompleted = null;
        if (isset($project->events)) {
            [$events, $eventsCompleted] = $project->events->partition(function ($event) {
                return GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event);
            });
        }

        // TODO change to stat table count
        $transcriptionsCount = CountHelper::projectTranscriptionCount($project->id);
        $transcribersCount = CountHelper::projectTranscriberCount($project->id);

        $years = $chartService->setYearsArray($project->id);

        $states = $stateCountyRepo->getStateTranscriptCount($project->id);
        $max = abs(round(($states->max('value') + 500), -3));

        JavaScript::put([
            'max'     => $max,
            'states'  => $states->toJson(),
            'years'   => $years === null ? null : $years->toArray(),
            'project' => $project->id,
        ]);

        return view('front.project.home', compact('project', 'years', 'expeditions', 'expeditionsCompleted', 'events', 'eventsCompleted', 'transcriptionsCount', 'transcribersCount'));
    }
}
