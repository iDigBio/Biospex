<?php 

namespace App\Http\Controllers\Frontend;

use App\Events\PollOcrEvent;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\OcrQueue;
use DOMDocument;
use GuzzleHttp\Pool;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Events\Dispatcher;
use MongoCollection;

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
     * Constructor
     *
     * @internal param Curl $curl
     */
    public function __construct()
    {
        $this->ocrDeleteUrl = Config::get('config.ocrDeleteUrl');
        $this->client = New Client();
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
        $user = Request::user();

        if ( ! $user->isAdmin('admins')) {
            return redirect()->route('login');
        }

        ob_start();
        phpinfo();
        $phpinfo = ob_get_contents();
        ob_end_clean();

        $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);

        return view('frontend.info', compact('info'));
    }

    public function clear()
    {
        $user = Request::user();

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
        if (Request::isMethod('post') && ! empty(Request::get('files'))) {
            $files = Request::get('files');
            $requests = [];
            foreach ($files as $file)
            {
                $options = [
                    'headers' => ['Content-Type' => 'multipart/form-data', 'API-KEY' => 't$p480UAJ5v8P=ifcE23&hpM?#+&r3']
                ];
                $newRequest = $this->client->createRequest('POST', Config::get('config.ocrDeleteUrl'), $options);
                $postBody = $newRequest->getBody();
                $postBody->setField('file', $file);

                $requests[] = $newRequest;
            }

            Pool::send($this->client, $requests, ['pool_size' => 10]);
        }

        $html = file_get_contents(Request::get('config.ocrGetUrl'));

        $dom = new DomDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);

        $elements = $dom->getElementsByTagName('li');

        return view('frontend.ocr', compact('elements'));
    }
    
    public function pollOcr(Dispatcher $dispatcher, OcrQueue $ocrQueue)
    {
        if (Request::ajax()) {

            $dispatcher->fire(new PollOcrEvent($ocrQueue));
        }
    }

    public function test()
    {
        
    }
}
