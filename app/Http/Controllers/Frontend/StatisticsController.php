<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Interfaces\PanoptesTranscription;
use App\Interfaces\Project;
use JavaScript;

class StatisticsController extends Controller
{

    /**
     * @var Project
     */
    private $projectContract;
    /**
     * @var PanoptesTranscription
     */
    private $panoptesTranscriptionContract;

    /**
     * DownloadsController constructor.
     * @param Project $projectContract
     * @param PanoptesTranscription $panoptesTranscriptionContract
     */
    public function __construct(
        Project $projectContract,
        PanoptesTranscription $panoptesTranscriptionContract
    )
    {
        $this->projectContract = $projectContract;
        $this->panoptesTranscriptionContract = $panoptesTranscriptionContract;
    }

    /**
     * @param $projectId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($projectId)
    {
        $project = $this->projectContract->find($projectId);
        $transcribers = collect($this->panoptesTranscriptionContract
            ->getUserTranscriptionCount($projectId))->sortByDesc('transcriptionCount');

        $plucked = collect(array_count_values($transcribers->pluck('transcriptionCount')->sort()->toArray()));

        $transcriptions = $plucked->flatMap(function($users, $count){
            return [['count' => $count, 'users' => $users]];
        })->toJson();

        JavaScript::put([
            'transcriptionChartData' => $transcriptions
        ]);

        return view('frontend.statistics.index', compact('project', 'transcribers'));
    }
}
