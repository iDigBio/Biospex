<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\CountHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\PanoptesTranscription;
use App\Repositories\Interfaces\Project;
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
        $project = $this->projectContract->findWith($projectId, ['group']);

        $transcribers = CountHelper::getUserTranscriptionCount($projectId)->sortByDesc('transcriptionCount');

        $transcriptions = \Cache::tags('panoptes'.$projectId)->remember(md5(__METHOD__.$projectId), 43200, function () use
        (
            $transcribers
        ) {
            return $transcribers->isEmpty() ? null : $transcribers->pluck('transcriptionCount')->pipe(function ($transcribers) {
                return collect(array_count_values($transcribers->sort()->toArray()));
            })->flatMap(function ($users, $count) {
                return [['transcriptions' => $count, 'transcribers' => $users]];
            })->toJson();
        });

        return view('frontend.statistics.index', compact('project', 'transcribers', 'transcriptions'));
    }
}
