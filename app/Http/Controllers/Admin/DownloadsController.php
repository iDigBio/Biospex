<?php

namespace App\Http\Controllers\Admin;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Jobs\ActorJob;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\User;
use App\Repositories\Interfaces\Download;
use GeneralHelper;
use Illuminate\Support\Facades\Storage;

class DownloadsController extends Controller
{
    /**
     * Index showing downloads for Expedition.
     *
     * @param \App\Repositories\Interfaces\User $userContract
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param $projectId
     * @param $expeditionId
     * @return array|\Illuminate\Contracts\View\Factory|\Illuminate\View\View|string|null
     */
    public function index(User $userContract, Expedition $expeditionContract, $projectId, $expeditionId)
    {
        $user = $userContract->findWith(request()->user()->id, ['profile']);
        $expedition = $expeditionContract->expeditionDownloadsByActor($projectId, $expeditionId);

        $error = ! $this->checkPermissions('readProject', $expedition->project->group) ? true : false;

        return view('admin.partials.expedition-download-modal-body', compact('expedition', 'user', 'error'));
    }

    /**
     * Show downloads.
     *
     * @param \App\Repositories\Interfaces\Download $downloadContract
     * @param $projectId
     * @param $expeditionId
     * @param $downloadId
     * @return array|\Illuminate\Http\Response|string|\Symfony\Component\HttpFoundation\StreamedResponse|null
     * @throws \Throwable
     */
    public function download(Download $downloadContract, $projectId, $expeditionId, $downloadId)
    {
        try {

            $download = $downloadContract->findWith($downloadId, ['expedition.project.group']);

            if (! $download) {
                FlashHelper::error(trans('messages.missing_download_file'));

                return redirect()->back();
            }

            if ($download->type !== 'export' && ! $this->checkPermissions('isOwner', $download->expedition->project->group)) {
                return redirect()->back();
            }

            $download->count = $download->count + 1;
            $downloadContract->update($download->toArray(), $download->id);

            if (! empty($download->data)) {
                $headers = [
                    'Content-type'        => 'application/json; charset=utf-8',
                    'Content-disposition' => 'attachment; filename="'.$download->file.'"',
                ];

                $view = view('frontend.manifest', unserialize($download->data))->render();

                return response()->make(stripslashes($view), 200, $headers);
            }

            if (! GeneralHelper::downloadFileExists($download->type, $download->file)) {
                FlashHelper::error(trans('messages.missing_download_file'));

                return redirect()->route('webauth.downloads.index', [$projectId, $expeditionId]);
            }

            $headers = [
                'Content-Type'        => 'application/x-compressed',
                'Content-disposition' => 'attachment; filename="'.$download->type.'-'.$download->file.'"',
            ];

            $file = Storage::path(config('config.nfn_downloads_dir').'/'.$download->type.'/'.$download->file);

            return response()->download($file, $download->type.'-'.$download->file, $headers);
        } catch (\Exception $e) {
            FlashHelper::error(__($e->getMessage()));

            return redirect()->back();
        }
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
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse|string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function summary(Expedition $expeditionContract, $projectId, $expeditionId)
    {
        $expedition = $expeditionContract->findwith($expeditionId, ['project.group']);

        if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
            return redirect()->route('webauth.projects.show', [$projectId]);
        }

        if (! Storage::exists(config('config.nfn_downloads_summary').'/'.$expeditionId.'.html')) {
            FlashHelper::warning(trans('pages.file_does_not_exist'));

            return redirect()->back();
        }

        return Storage::get(config('config.nfn_downloads_summary').'/'.$expeditionId.'.html');
    }
}
