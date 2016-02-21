<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use DOMDocument;
use GuzzleHttp\Pool;
use Illuminate\Config\Repository as Config;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ServerInfoController extends Controller
{
    /**
     * @var mixed
     */
    protected $ocrDeleteUrl;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Constructor
     *
     * @param Config $config
     * @param Request $request
     * @internal param Curl $curl
     */
    public function __construct(
        Config $config,
        Request $request
    )
    {
        $this->ocrDeleteUrl = $this->config->get('config.ocrDeleteUrl');
        $this->client = New Client();
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Test $_POST
     */
    public function postTest()
    {
        http_response_code(200);

        exit;
    }

    /**
     * Test $_GET
     */
    public function getTest()
    {
        http_response_code(200);

        exit;
    }

    /**
     * Display php info
     */
    public function showPhpInfo()
    {
        $user = $this->request->user();

        if ( ! $user->isAdmin('admins')) {
            return redirect()->route('login');
        }

        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();

        $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);

        return view('frontend.info', compact(['info']));
    }

    public function clear()
    {
        $user = $this->request->user();

        if ( ! $user->isAdmin('admins')) {
            return redirect()->route('login');
        }

        Cache::flush();
        // Need to curl to server and run php script to clear memcache

        session_flash_push('success', "Cache has been flushed.");

        return redirect()->intended('/projects');
    }

    public function ocr()
    {
        if ($this->request->isMethod('post') && ! empty($this->request->get('files'))) {
            $files = $this->request->get('files');
            $requests = [];
            foreach ($files as $file)
            {
                $options = [
                    'headers' => ['Content-Type' => 'multipart/form-data', 'API-KEY' => 't$p480UAJ5v8P=ifcE23&hpM?#+&r3']
                ];
                $newRequest = $this->client->createRequest('POST', $this->config->get('config.ocrDeleteUrl'), $options);
                $postBody = $newRequest->getBody();
                $postBody->setField('file', $file);

                $requests[] = $newRequest;
            }

            Pool::send($this->client, $requests, ['pool_size' => 10]);
        }

        $html = file_get_contents($this->request->get('config.ocrGetUrl'));

        $dom = new DomDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);

        $elements = $dom->getElementsByTagName('li');

        return view('frontend.ocr', compact('elements'));
    }
}
