<?php namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use DOMDocument;
use Illuminate\Http\Request;
use Illuminate\Config\Repository as Config;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{

    /**
     * @var Request
     */
    private $request;
    
    /**
     * @var Config
     */
    private $config;

    /**
     * Create a new controller instance.
     *
     * @param Request $request
     * @param Client $client
     * @param Config $config
     */
    public function __construct(Request $request, Config $config)
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Show the application dashboard.
     *
     * @return Response
     */
    public function index()
    {
        return view('backend.index');
    }

    public function ocr()
    {
        if ($this->request->isMethod('post') && ! empty($this->request->get('files')))
        {
            $files = $this->request->get('files');
            
            Artisan::call('ocrfile:delete', ['files' => $files]);
        }

        $html = file_get_contents($this->config->get('config.ocr_get_url'));

        $dom = new DomDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);

        $elements = $dom->getElementsByTagName('li');

        return view('backend.ocr', compact('elements'));
    }

    public function phpinfo()
    {
        return view('backend.phpinfo');
    }
}