<?php namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Curl\Curl;

class ServerInfoController extends Controller
{
    /**
     * Constructor
     *
     * @param Curl $curl
     */
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['showPhpInfo', 'clear', 'ocr']]);
        $this->middleware('acl:create-project', ['only' => ['create', 'store']]);
        $this->middleware('acl:update-project', ['only' => ['edit', 'update']]);
        $this->middleware('acl:delete-project', ['only' => ['destroy']]);
        $this->ocrDeleteUrl = \Config::get('config.ocr_delete_url');
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
        if (!Sentry::getUser()->isSuperUser()) {
            return Redirect::route('login');
        }

        ob_start();
        phpinfo();
        $pinfo = ob_get_contents();
        ob_end_clean();

        $info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);

        return View::make('info', compact(['info']));
    }

    public function clear()
    {
        if (!Sentry::getUser()->isSuperUser()) {
            return Redirect::route('login');
        }

        Cache::flush();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Config::get('app.ip') . '/cache.php');
        curl_exec($ch);
        curl_close($ch);

        Session::flash('success', "Cache has been flushed.");

        return Redirect::intended('/projects');
    }

    public function ocr()
    {
        if (Request::isMethod('post') &&  ! empty(Input::get('files'))) {
            $files = Input::get('files');
            $rc = new Curl();
            $rc->window_size = 5;
            foreach ($files as $file) {
                $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_FAILONERROR, true];
                $headers = ['Content-Type: application/x-www-form-urlencoded', 'API-KEY:t$p480UAJ5v8P=ifcE23&hpM?#+&r3'];
                $data = "file=$file";
                $rc->post($this->ocrDeleteUrl, $data, $headers, $options);
            }

            $rc->execute();
        }

        $html = file_get_contents("http://ocr.dev.morphbank.net/status");

        $dom = new DomDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);

        $elements = $dom->getElementsByTagName('li');


        return View::make('ocr', compact('elements'));
    }
}
