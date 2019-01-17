<?php

namespace App\Http\Controllers\Admin;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Jobs\ActorJob;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\User;
use Illuminate\Contracts\Routing\ResponseFactory;
use App\Repositories\Interfaces\Download;
use Illuminate\Support\Facades\Storage;

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
    ) {
        $this->expeditionContract = $expeditionContract;
        $this->downloadContract = $downloadContract;
        $this->response = $response;
        $this->userContract = $userContract;

        $this->paths = [
            'export'          => Storage::path(config('config.export_dir')),
            'classifications' => Storage::path(config('config.classifications_download')),
            'transcriptions'  => Storage::path(config('config.classifications_transcript')),
            'reconciled'      => Storage::path(config('config.classifications_reconcile')),
            'summary'         => Storage::path(config('config.classifications_summary')),
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

        if (! $this->checkPermissions('readProject', $expedition->project->group)) {
            return __('You do not have sufficient permissions.');
        }

        $paths = $this->paths;

        return view('admin.partials.expedition-download-modal-body', compact('expedition', 'user', 'paths'));
    }

    /**
     * Show downloads.
     *
     * @param $projectId
     * @param $expeditionId
     * @param $downloadId
     * @return array|\Illuminate\Http\Response|string|\Symfony\Component\HttpFoundation\StreamedResponse|null
     * @throws \Throwable
     */
    public function download($projectId, $expeditionId, $downloadId)
    {
        $download = $this->downloadContract->findWith($downloadId, ['expedition.project.group']);

        if (! $download) {
            //FlashHelper::error(trans('messages.missing_download_file'));
            //return redirect()->route('webauth.downloads.index', [$projectId, $expeditionId]);
            return __('The file appears to be missing. If you\'d like to have the file regenerated, please contact the Biospex Administrator using the contact form and specify the Expedition title.');
        }

        if ($download->type !== 'export' && ! $this->checkPermissions('isOwner', $download->expedition->project->group)) {
            //return redirect()->route('web.projects.index');
            return __('You do not have sufficient permissions.');
        }

        /*
        header('Set-Cookie: fileDownload=true; path=/');
        header('Cache-Control: max-age=60, must-revalidate');
        header("Content-type: text/csv");
        header('Content-Disposition: attachment; filename="'.$title.'-' . $timestamp . '.csv"');
         */

        $download->count = $download->count + 1;
        $this->downloadContract->update($download->toArray(), $download->id);

        if (! empty($download->data)) {
            $headers = [
                'Set-Cookie' => 'fileDownload=true; path=/',
                'Cache-Control' => 'max-age=60, must-revalidate',
                'Content-type'        => 'application/json; charset=utf-8',
                'Content-disposition' => 'attachment; filename="'.$download->file.'"',
            ];

            $view = view('front.manifest', unserialize($download->data))->render();

            return $this->response->make(stripslashes($view), 200, $headers);
        }

        $path = $this->paths[$download->type].'/'.$download->file;
        if (! file_exists($path)) {
            //FlashHelper::error(trans('messages.missing_download_file'));
            //return redirect()->route('webauth.downloads.index', [$projectId, $expeditionId]);
            return __('The file appears to be missing. If you\'d like to have the file regenerated, please contact the Biospex Administrator using the contact form and specify the Expedition title.');
        }

        $headers = [
            'Set-Cookie' => 'fileDownload=true; path=/',
            'Cache-Control' => 'max-age=60, must-revalidate',
            'Content-Type'        => 'application/x-compressed',
            'Content-disposition' => 'attachment; filename="'.$download->type.'-'.$download->file.'"',
        ];

        return Storage::download($path, $download->type.'-'.$download->file, $headers);

        //return $this->response->download($path, $download->type.'-'.$download->file, $headers);
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

        try {
            $expedition->nfnActor->pivot->state = 0;
            $expedition->nfnActor->pivot->total = $expedition->stat->local_subject_count;
            $expedition->nfnActor->pivot->processed = 0;
            $expedition->nfnActor->pivot->queued = 1;
            event('actor.pivot.regenerate', [$expedition->nfnActor]);

            ActorJob::dispatch(serialize($expedition->nfnActor));

            FlashHelper::success(trans('messages.download_regeneration_success'));
        } catch (\Exception $e) {
            FlashHelper::error(trans('messages.download_regeneration_error', ['error' => $e->getMessage()]));
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

        if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
            return __('You do not have sufficient permissions.');
            //return redirect()->route('webauth.projects.show', [$projectId]);
        }

        if (! Storage::exists(config('config.classifications_summary').'/'.$expeditionId.'.html')) {
            //FlashHelper::warning(trans('pages.file_does_not_exist'));
            //return redirect()->route('webauth.projects.show', [$projectId]);

            return __('File does not exist');
        }



        return Storage::get(config('config.classifications_summary').'/'.$expeditionId.'.html');
    }
}
