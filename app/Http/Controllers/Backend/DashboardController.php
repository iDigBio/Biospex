<?php namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use DOMDocument;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Config\Repository as Config;

class DashboardController extends Controller
{

    /**
     * @var Request
     */
    private $request;
    /**
     * @var Client
     */
    private $client;
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

        $this->client = New Client();
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
            foreach ($files as $file)
            {
                $response = $this->client->request('POST', $this->config->get('config.ocr_delete_url'), [
                    'multipart' => [
                        [
                            'name'     => 'file',
                            'contents' => $file,
                            'headers'  => [
                                'API-KEY' => 't$p480UAJ5v8P=ifcE23&hpM?#+&r3'
                            ]
                        ]
                    ]]);

                dd($response->getStatusCode());
            }
        }

        $html = file_get_contents($this->config->get('config.ocr_get_url'));

        $dom = new DomDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);

        $elements = $dom->getElementsByTagName('li');

        return view('backend.ocr', compact('elements'));
    }
}