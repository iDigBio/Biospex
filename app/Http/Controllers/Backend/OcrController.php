<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Contracts\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use DOMDocument;

class OcrController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, User $repo)
    {
        $user = $repo->with(['profile'])->find($request->user()->id);

        $html = file_get_contents(Config::get('config.ocr_get_url'));

        $dom = new DomDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_use_internal_errors(false);

        $elements = $dom->getElementsByTagName('li');

        return view('backend.ocr', compact('elements', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        if ($request->isMethod('post') && ! empty($request->get('files')))
        {
            $files = $request->get('files');

            Artisan::call('ocrfile:delete', ['files' => $files]);

            session_flash_push('success', 'Ocr files deleted successfully.');

            return redirect()->route('ocr.get.index');
        }

        session_flash_push('error', 'Ocr files could not be deleted.');

        return redirect()->route('ocr.get.index');
    }
}
