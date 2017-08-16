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

class DownloadsController extends Controller
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
     * @var array
     */
    public $paths;

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

        $this->paths = [
            'export' => config('config.nfn_export_dir'),
            'classifications' => config('config.classifications_download'),
            'transcriptions' => config('config.classifications_transcript'),
            'reconciled' => config('config.classifications_reconcile'),
            'summary' => config('config.classifications_summary')
        ];
    }

    /**
     * Index showing downloads for Expedition.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\View\View
     */
    public function index($projectId, $expeditionId)
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $expedition = $this->expeditionContract->expeditionDownloadsByActor($expeditionId);
        $paths = $this->paths;

        return view('frontend.downloads.index', compact('expedition', 'user', 'paths'));
    }

    /**
     * Show downloads.
     *
     * @param $projectId
     * @param $expeditionId
     * @param $downloadId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show($projectId, $expeditionId, $downloadId)
    {
        $download = $this->downloadContract->find($downloadId);

        if ( ! $download)
        {
            session_flash_push('error', trans('errors.missing_download_file'));
            return redirect()->route('web.downloads.index', [$projectId, $expeditionId]);
        }

        $download->count = $download->count + 1;
        $this->downloadContract->update($download->id, $download->toArray());

        if ( ! empty($download->data))
        {
            $headers = [
                'Content-type'        => 'application/json; charset=utf-8',
                'Content-disposition' => 'attachment; filename="' . $download->file . '"'
            ];

            $view = view('frontend.manifest', unserialize($download->data))->render();

            return $this->response->make(stripslashes($view), 200, $headers);
        }
        else
        {
            $path = $this->paths[$download->type] . '/' . $download->file;
            if ( ! file_exists($path))
            {
                session_flash_push('error', trans('errors.missing_download_file'));
                return redirect()->route('web.downloads.index', [$projectId, $expeditionId]);
            }

            $headers = [
                'Content-Type'        => 'application/x-compressed',
                'Content-disposition' => 'attachment; filename="' . $download->type . '-' . $download->file . '"'
            ];

            return $this->response->download($path, $download->type . '-' . $download->file, $headers);
        }
    }

    /**
     * Regenerate export download.
     *
     * @param ExpeditionContract $expeditionContract
     * @param Handler $handler
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerate(ExpeditionContract $expeditionContract, Handler $handler, $projectId, $expeditionId)
    {
        $withRelations = ['nfnActor', 'stat'];

        $expedition = $expeditionContract->setCacheLifetime(0)->with($withRelations)->find($expeditionId);

        try
        {
            $expedition->nfnActor->pivot->state = 0;
            $expedition->nfnActor->pivot->total = $expedition->stat->subject_count;
            $expedition->nfnActor->pivot->processed = 0;
            $expedition->nfnActor->pivot->queued = 1;
            event('actor.pivot.regenerate', [$expedition->nfnActor]);
            Queue::push('App\Services\Queue\ActorQueue', serialize($expedition->nfnActor), config('config.beanstalkd.export'));

            session_flash_push('success', trans('expeditions.download_regeneration_success'));
        }
        catch (BiospexException $e)
        {
            $handler->report($e);
            session_flash_push('error', trans('expeditions.download_regeneration_error', ['error' => $e->getMessage()]));
        }

        return redirect()->route('web.downloads.index', [$projectId, $expeditionId]);
    }

    /**
     * Display the summary page.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse|string
     */
    public function summary($projectId, $expeditionId)
    {
        $expedition = $this->expeditionContract->with('project.group')->find($expeditionId);

        if ( ! $this->checkPermissions('isOwner', [$expedition->project->group]))
        {
            return redirect()->route('web.projects.show', [$projectId]);
        }

        if ( ! File::exists(config('config.classifications_summary') . '/' . $expeditionId . '.html'))
        {
            session_flash_push('warning', trans('pages.file_does_not_exist'));
            return redirect()->route('web.projects.show', [$projectId]);
        }

        return File::get(config('config.classifications_summary') . '/' . $expeditionId . '.html');
    }
}
