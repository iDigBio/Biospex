<?php namespace Biospex\Http\Controllers\Front;

use Biospex\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Config\Repository as Config;
use Biospex\Repositories\Contracts\Expedition;
use Biospex\Repositories\Contracts\Download;

class DownloadsController extends Controller
{
    /**
     * @var ExpeditionInterface
     */
    protected $expedition;

    /**
     * @var DownloadInterface
     */
    protected $download;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Instantiate a new DownloadsController.
     *
     * @param ExpeditionInterface|Expedition $expedition
     * @param DownloadInterface|Download $download
     * @param Request $request
     * @param ResponseFactory $response
     * @param Config $config
     */
    public function __construct(
        Expedition $expedition,
        Download $download,
        Request $request,
        ResponseFactory $response,
        Config $config
    ) {
        $this->expedition = $expedition;
        $this->download = $download;
        $this->request = $request;
        $this->response = $response;
        $this->config = $config;
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
        $user = $this->request->user();
        $expedition = $this->expedition->findWith($expeditionId, ['project.group', 'downloads.actor']);

        return view('front.downloads.index', compact('expedition', 'user'));
    }

    /**
     * @param $projectId
     * @param $expeditionId
     * @param $downloadId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \Exception
     * @throws \Throwable
     */
    public function show($projectId, $expeditionId, $downloadId)
    {
        $download = $this->download->find($downloadId);
        $download->count = $download->count + 1;
        $download->save();

        if ( ! empty($download->data)){
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Content-disposition' => 'attachment; filename="' . $download->file . '"'
            ];

            $view = view('front.manifest', unserialize($download->data))->render();

            return $this->response->make(stripslashes($view), 200, $headers);
        } else {

            $nfnExportDir = $this->config->get('config.nfn_export_dir');
            $path = $nfnExportDir . '/' . $download->file;
            $headers = ['Content-Type' => 'application/x-compressed'];

            return $this->response->download($path, $download->file, $headers);
        }
    }
}
