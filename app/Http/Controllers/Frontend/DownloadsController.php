<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\User;
use File;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Repositories\Interfaces\Download;
use Queue;

class DownloadsController extends Controller
{

    /**
     * @var Expedition
     */
    public $expeditionContract;

    /**
     * @var Download
     */
    public $downloadContract;

    /**
     * @var ResponseFactory
     */
    public $response;

    /**
     * @var User
     */
    public $userContract;

    /**
     * @var array
     */
    public $paths;

    /**
     * DownloadsController constructor.
     *
     * @param Expedition $expeditionContract
     * @param Download $downloadContract
     * @param User $userContract
     * @param ResponseFactory $response
     * @internal param ResponseFactory $response0
     */
    public function __construct(
        Expedition $expeditionContract,
        Download $downloadContract,
        User $userContract,
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($projectId, $expeditionId)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $expedition = $this->expeditionContract->expeditionDownloadsByActor($projectId, $expeditionId);

        if ( ! $this->checkPermissions('read', $expedition->project))
        {
            return redirect()->route('web.projects.index');
        }

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
     * @throws \Throwable
     */
    public function show($projectId, $expeditionId, $downloadId)
    {
        $download = $this->downloadContract->findWith($downloadId, ['expedition.project.group']);

        if ( ! $download)
        {
            Flash::error(trans('messages.missing_download_file'));
            return redirect()->route('webauth.downloads.index', [$projectId, $expeditionId]);
        }

        if ($download->type !== 'export' && ! $this->checkPermissions('isOwner', $download->expedition->project->group))
        {
            return redirect()->route('web.projects.index');
        }

        $download->count = $download->count + 1;
        $this->downloadContract->update($download->toArray(), $download->id);

        if ( ! empty($download->data))
        {
            $headers = [
                'Content-type'        => 'application/json; charset=utf-8',
                'Content-disposition' => 'attachment; filename="' . $download->file . '"'
            ];

            $view = view('frontend.manifest', unserialize($download->data))->render();

            return $this->response->make(stripslashes($view), 200, $headers);
        }

        $path = $this->paths[$download->type] . '/' . $download->file;
        if ( ! file_exists($path))
        {
            Flash::error(trans('messages.missing_download_file'));
            return redirect()->route('webauth.downloads.index', [$projectId, $expeditionId]);
        }

        $headers = [
            'Content-Type'        => 'application/x-compressed',
            'Content-disposition' => 'attachment; filename="' . $download->type . '-' . $download->file . '"'
        ];

        return $this->response->download($path, $download->type . '-' . $download->file, $headers);
    }

    /**
     * Regenerate export download.
     *
     * @param Expedition $expeditionContract
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function regenerate(Expedition $expeditionContract, $projectId, $expeditionId)
    {
        $withRelations = ['nfnActor', 'stat'];

        $expedition = $expeditionContract->findWith($expeditionId, $withRelations);

        try
        {
            $expedition->nfnActor->pivot->state = 0;
            $expedition->nfnActor->pivot->total = $expedition->stat->local_subject_count;
            $expedition->nfnActor->pivot->processed = 0;
            $expedition->nfnActor->pivot->queued = 1;
            event('actor.pivot.regenerate', [$expedition->nfnActor]);
            Queue::push('App\Services\Queue\ActorQueue', serialize($expedition->nfnActor), config('config.beanstalkd.export'));

            Flash::success(trans('messages.download_regeneration_success'));
        }
        catch (\Exception $e)
        {
            Flash::error(trans('messages.download_regeneration_error', ['error' => $e->getMessage()]));
        }

        return redirect()->route('webauth.downloads.index', [$projectId, $expeditionId]);
    }

    /**
     * Display the summary page.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse|string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function summary($projectId, $expeditionId)
    {
        $expedition = $this->expeditionContract->findwith($expeditionId, ['project.group']);

        if ( ! $this->checkPermissions('isOwner', $expedition->project->group))
        {
            return redirect()->route('webauth.projects.show', [$projectId]);
        }

        if ( ! File::exists(config('config.classifications_summary') . '/' . $expeditionId . '.html'))
        {
            Flash::warning( trans('pages.file_does_not_exist'));
            return redirect()->route('webauth.projects.show', [$projectId]);
        }

        return File::get(config('config.classifications_summary') . '/' . $expeditionId . '.html');
    }
}
