<?php

namespace App\Http\Controllers\Frontend;

use App\Exceptions\Handler;
use App\Exceptions\BiospexException;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\UserContract;
use File;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Repositories\Contracts\DownloadContract;
use Queue;

class TranscriptionsController extends Controller
{

    /**
     * @var ExpeditionContract
     */
    public $expeditionContract;

    /**
     * @var DownloadContract
     */
    public $downloadContract;

    /**
     * @var ResponseFactory
     */
    public $response;

    /**
     * @var UserContract
     */
    public $userContract;

    /**
     * DownloadsController constructor.
     *
     * @param ExpeditionContract $expeditionContract
     * @param DownloadContract $downloadContract
     * @param UserContract $userContract
     * @param ResponseFactory $response
     * @internal param ResponseFactory $response0
     */
    public function __construct(
        ExpeditionContract $expeditionContract,
        DownloadContract $downloadContract,
        UserContract $userContract,
        ResponseFactory $response
    )
    {
        $this->expeditionContract = $expeditionContract;
        $this->downloadContract = $downloadContract;
        $this->response = $response;
        $this->userContract = $userContract;
    }

    /**
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index($projectId, $expeditionId)
    {
        $expedition = $this->expeditionContract->with('project.group')->find($expeditionId);

        if ( ! $this->checkPermissions('isOwner', $expedition->project->group))
        {
            return redirect()->route('web.projects.show', [$projectId]);
        }

        return view('frontend.transcriptions.index', compact('expedition'));
    }

    public function download()
    {



    }
}
