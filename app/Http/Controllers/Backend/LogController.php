<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\User;
use App\Services\LogViewer;

class LogController extends Controller
{
    protected $request;
    /**
     * @var User
     */
    private $userContract;

    /**
     * LogController constructor.
     * @param User $userContract
     */
    public function __construct (User $userContract)
    {
        $this->request = app('request');
        $this->userContract = $userContract;
    }

    public function index()
    {
        if ($this->request->input('l')) {
            LogViewer::setFile(base64_decode($this->request->input('l')));
        }

        if ($this->request->input('dl')) {
            return $this->download(LogViewer::pathToLogFile(base64_decode($this->request->input('dl'))));
        } elseif ($this->request->has('del')) {
            app('files')->delete(LogViewer::pathToLogFile(base64_decode($this->request->input('del'))));
            app('files')->put(LogViewer::pathToLogFile(base64_decode($this->request->input('del'))),'');
            return $this->redirect($this->request->url());
        } elseif ($this->request->has('delall')) {
            foreach(LogViewer::getFiles(true) as $file){
                app('files')->delete(LogViewer::pathToLogFile($file));
            }
            return $this->redirect($this->request->url());
        }

        $logs = LogViewer::all();
        $files = LogViewer::getFiles(true);
        $current_file = LogViewer::getFileName();

        $user = $this->userContract->findWith(request()->user()->id, ['profile']);

        return view('backend.servers.log', compact('logs', 'files', 'current_file', 'user'));
    }

    private function redirect($to)
    {
        if (function_exists('redirect')) {
            return redirect($to);
        }

        return app('redirect')->to($to);
    }

    private function download($data)
    {
        if (function_exists('response')) {
            return response()->download($data);
        }

        // For laravel 4.2
        return app('\Illuminate\Support\Facades\Response')->download($data);
    }
}