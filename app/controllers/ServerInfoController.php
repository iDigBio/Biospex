<?php

/**
 * ServerInfoController.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Biospex\Services\Curl\Curl;

class ServerInfoController extends BaseController
{
    /**
     * Constructor
     *
     * @param Curl $curl
     */
    public function __construct()
    {
        $this->beforeFilter('auth', ['only' => ['showPhpInfo', 'clear', 'ocr']]);
        $this->ocrDeleteUrl = \Config::get('config.ocrDeleteUrl');
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
        if (! Sentry::getUser()->isSuperUser()) {
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
        if (! Sentry::getUser()->isSuperUser()) {
            return Redirect::route('login');
        }

        Cache::flush();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Config::get('config.ip') . '/cache.php');
        curl_exec($ch);
        curl_close($ch);

        Session::flash('success', "Cache has been flushed.");

        return Redirect::intended('/projects');
    }

    public function ocr()
    {
        if (Request::isMethod('post') && ! empty(Input::get('files'))) {
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
