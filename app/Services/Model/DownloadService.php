<?php

namespace App\Services\Model;

use App\Jobs\ActorJob;
use App\Repositories\Interfaces\Download;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\User;
use File;
use Storage;

class DownloadService
{
    /**
     * @var \App\Repositories\Interfaces\User
     */
    private $userContract;

    /**
     * @var \App\Repositories\Interfaces\Expedition
     */
    private $expeditionContract;

    /**
     * @var \App\Repositories\Interfaces\Download
     */
    private $downloadContract;

    /**
     * DownloadService constructor.
     *
     * @param \App\Repositories\Interfaces\User $userContract
     * @param \App\Repositories\Interfaces\Expedition $expeditionContract
     * @param \App\Repositories\Interfaces\Download $downloadContract
     */
    public function  __construct (
        User $userContract,
        Expedition $expeditionContract,
        Download $downloadContract
    )
    {

        $this->userContract = $userContract;
        $this->expeditionContract = $expeditionContract;
        $this->downloadContract = $downloadContract;
    }

    /**
     * Get user info and profile for download index.
     *
     * @param int $userId
     * @return \App\Models\User
     */
    public function getUserProfile(int $userId): \App\Models\User
    {
        return $this->userContract->findWith($userId, ['profile']);
    }

    /**
     * Get expedition by id.
     *
     * @param string $expeditionId
     * @param array|null $relations
     * @return \App\Models\Expedition
     */
    public function getExpeditionById(string $expeditionId, array $relations = null): \App\Models\Expedition
    {
        return $this->expeditionContract->findwith($expeditionId, $relations);
    }

    /**
     * Get expedition info by actors.
     *
     * @param string $projectId
     * @param string $expeditionId
     * @return \App\Models\Expedition
     */
    public function getExpeditionByActor(string $projectId, string $expeditionId): \App\Models\Expedition
    {
        return $this->expeditionContract->expeditionDownloadsByActor($projectId, $expeditionId);
    }

    /**
     * Get download.
     *
     * @param string $downloadId
     * @return \App\Models\Download
     */
    public function getDownload(string $downloadId): \App\Models\Download
    {
        return $this->downloadContract->findWith($downloadId, ['expedition.project.group']);
    }

    /**
     * Update the download count.
     *
     * @param \App\Models\Download $download
     */
    public function updateDownloadCount(\App\Models\Download $download)
    {
        $download->count = $download->count + 1;
        $this->downloadContract->update($download->toArray(), $download->id);
    }

    /**
     * Created data download.
     *
     * @param \App\Models\Download $download
     * @return array
     * @throws \Throwable
     */
    public function createDataView(\App\Models\Download $download): array
    {
        $headers = [
            'Content-type'        => 'application/json; charset=utf-8',
            'Content-disposition' => 'attachment; filename="'.$download->file.'"',
        ];

        $view = view('frontend.manifest', unserialize($download->data))->render();

        return [$view, $headers];
    }

    /**
     * Created download file.
     *
     * @param \App\Models\Download $download
     * @return array
     */
    public function createDownloadFile(\App\Models\Download $download)
    {
        $headers = [
            'Content-Type'        => 'application/x-compressed',
            'Content-disposition' => 'attachment; filename="'.$download->type.'-'.$download->file.'"',
        ];

        $path = $download->type === 'export' ?
            config('config.export_dir') :
            config('config.nfn_downloads_dir').'/'.$download->type;

        $file = Storage::path($path.'/'.$download->file);

        return [$headers, $path, $file];
    }

    /**
     * Delete existing exports files for expedition.
     *
     * @param string $expeditionId
     */
    public function deleteExportFiles(string $expeditionId)
    {
        $downloads = $this->downloadContract->getExportFiles($expeditionId);
        $nfnExportDir = Storage::path(config('config.export_dir'));

        $downloads->each(function ($download) use($nfnExportDir)
        {
            $file = $nfnExportDir . '/' . $download->file;
            if (File::isFile($file))
            {
                File::delete($file);
            }

            $this->downloadContract->delete($download->id);
        });
    }

    /**
     * Reset data for expedition when regenerating export.
     *
     * @param string $expeditionId
     */
    public function resetExpeditionData(string $expeditionId)
    {
        $this->deleteExportFiles($expeditionId);

        $expedition = $this->getExpeditionById($expeditionId, ['nfnActor', 'stat']);
        $expedition->nfnActor->pivot->state = 0;
        $expedition->nfnActor->pivot->total = $expedition->stat->local_subject_count;
        $expedition->nfnActor->pivot->processed = 0;
        $expedition->nfnActor->pivot->queued = 1;

        event('actor.pivot.regenerate', [$expedition->nfnActor]);

        ActorJob::dispatch(serialize($expedition->nfnActor));
    }
}