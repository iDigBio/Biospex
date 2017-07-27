<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PanoptesTranscriptionContract;
use App\Repositories\Contracts\ProjectContract;
use JavaScript;

class StatisticsController extends Controller
{

    /**
     * @var ProjectContract
     */
    private $projectContract;
    /**
     * @var PanoptesTranscriptionContract
     */
    private $panoptesTranscriptionContract;


    /**
     * DownloadsController constructor.
     * @param ProjectContract $projectContract
     * @param PanoptesTranscriptionContract $panoptesTranscriptionContract
     */
    public function __construct(
        ProjectContract $projectContract,
        PanoptesTranscriptionContract $panoptesTranscriptionContract
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
            ->setCacheLifetime(0)
            ->getUserTranscriptionCount($projectId))->sortByDesc('count');
        $plucked = collect(array_count_values($transcribers->pluck('count')->sort()->toArray()));

        $transcriptions = $plucked->flatMap(function($users, $count){
            return [['count' => $count, 'users' => $users]];
        })->toJson();

        JavaScript::put([
            'transcriptionChartData' => $transcriptions
        ]);

        return view('frontend.statistics.index', compact('project', 'transcribers'));
    }
}
