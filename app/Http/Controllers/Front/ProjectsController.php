<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\StateCounty;
use App\Services\Process\TranscriptionChartService;
use CountHelper;
use GeneralHelper;
use Illuminate\Support\Carbon;
use JavaScript;

class ProjectsController extends Controller
{
    /**
     * @var \App\Repositories\Interfaces\Project
     */
    private $projectContract;

    /**
     * ProjectsController constructor.
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     */
    public function __construct(Project $projectContract)
    {

        $this->projectContract = $projectContract;
    }
    /**
     * Public Projects page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $projects = $this->projectContract->getPublicProjectIndex();

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
        $projects = $this->projectContract->getPublicProjectIndex($sort, $order);

        return view('front.project.partials.project', compact('projects'));
    }

    /**
     * Show public project page.
     *
     * @param \App\Services\Process\TranscriptionChartService $chartService
     * @param \App\Repositories\Interfaces\StateCounty $stateCountyContract
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function project(
        TranscriptionChartService $chartService,
        StateCounty $stateCountyContract,
        $slug
    )
    {
        $project = $this->projectContract->getProjectPageBySlug($slug);

        list($expeditions, $expeditionsCompleted) = $project->expeditions->partition(function ($expedition) {
            return $expedition->nfnActor->pivot->completed === 0;
        });

        list($events, $eventsCompleted) = $project->events->partition(function ($event) {
            return GeneralHelper::eventBefore($event) || GeneralHelper::eventActive($event);
        });

        $transcriptionsCount = CountHelper::projectTranscriptionCount($project->id);
        $transcribersCount = CountHelper::projectTranscriberCount($project->id);

        $years = $chartService->setYearsArray($project->id);

        $states = $stateCountyContract->getStateTranscriptCount($project->id);
        $max = abs(round(($states->max('value') + 500), -3));

        JavaScript::put([
            'max'    => $max,
            'states' => $states->toJson(),
            'years' => $years->toArray(),
            'project' => $project->id
        ]);

        return view('front.project.home', compact('project', 'years', 'expeditions', 'expeditionsCompleted', 'events', 'eventsCompleted', 'transcriptionsCount', 'transcribersCount'));
    }

    /**
     * State counties for project map.
     *
     * @param $projectId
     * @param $stateId
     * @param \App\Repositories\Interfaces\StateCounty $stateCounty
     * @return array
     */
    public function state($projectId, $stateId, StateCounty $stateCounty)
    {
        if (! request()->ajax()) {
            return response()->json(['html' => 'Error retrieving the counties.']);
        }

        $counties = $stateCounty->getCountyTranscriptionCount($projectId, $stateId)->map(function ($item) {
                return [
                    'id'    => str_pad($item->geo_id_2, 5, '0', STR_PAD_LEFT),
                    'value' => $item->transcription_locations_count,
                    'name'  => $item->state_county,
                ];
            });

        $dataArray = [
            'max'      => abs(round(($counties->max('value') + 500), -3)),
            'counties' => $counties->toJson(),
        ];

        return $dataArray;
    }
}
