<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Config\Repository as Config;
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
     * DownloadsController constructor.
     * 
     * @param Expedition $expedition
     * @param Download $download
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
        $expedition = $this->expedition->with(['project.group', 'downloads.actor'])->find($expeditionId);

        return view('frontend.downloads.index', compact('expedition', 'user'));
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
        $this->download->update($download->toArray(), $download->id);

        if ( ! empty($download->data)){
            $headers = [
                'Content-type' => 'application/json; charset=utf-8',
                'Content-disposition' => 'attachment; filename="' . $download->file . '"'
            ];

            $view = view('frontend.manifest', unserialize($download->data))->render();

            return $this->response->make(stripslashes($view), 200, $headers);
        } else {

            $nfnExportDir = $this->config->get('config.nfn_export_dir');
            $path = $nfnExportDir . '/' . $download->file;
            $headers = ['Content-Type' => 'application/x-compressed'];

            return $this->response->download($path, $download->file, $headers);
        }
    }
}
