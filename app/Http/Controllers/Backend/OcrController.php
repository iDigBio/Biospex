<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
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

        return view('backend.ocr.index', compact('elements', 'user'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        if (count($request->get('files')) > 0)
        {
            $files = $request->get('files');

            Artisan::call('ocrfile:delete', ['files' => $files]);

            Toastr::success('OCR deleted successfully.', 'OCR Delete');

            return redirect()->route('admin.ocr.index');
        }

        Toastr::error('Deleting did not work.', 'OCR Delete');

        return redirect()->route('admin.ocr.index');
    }
}
