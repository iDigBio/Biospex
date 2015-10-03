<?php namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Expedition;
use App\Repositories\Contracts\Download;

class DownloadsController extends Controller
{
    /**
     * @var Expedition
     */
    protected $expedition;

    /**
     * @var Download
     */
    protected $download;

    /**
     * Instantiate a new DownloadsController.
     *
     * @param Expedition $expedition
     * @param Download $download
     */
    public function __construct(
        Expedition $expedition,
        Download $download
    ) {
        $this->expedition = $expedition;
        $this->download = $download;

        // Establish Filters
        $this->beforeFilter('auth', ['only' => ['index']]);
        $this->beforeFilter('csrf', ['on' => 'post']);
        $this->beforeFilter('hasProjectAccess:expedition_view', ['only' => ['download', 'file']]);
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
        $user = \Sentry::getUser();
        $expedition = $this->expedition->findWith($expeditionId, ['project.group', 'downloads.actor']);
        return \View::make('downloads.index', compact('expedition', 'user'));
    }

    public function show($projectId, $expeditionId, $downloadId)
    {
        $download = $this->download->find($downloadId);
        $download->count = $download->count + 1;
        $this->download->save($download);

        $nfnExportDir = \Config::get('config.nfn_export_dir');
        $path = "$nfnExportDir/{$download->file}";
        $headers = ['Content-Type' => 'application/x-compressed'];
        return \Response::download($path, $download->file, $headers);
    }
}
