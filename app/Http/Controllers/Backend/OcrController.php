<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Repositories\Contracts\UserContract;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use DOMDocument;

class OcrController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param UserContract $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(UserContract $userContract)
    {
        $user = $userContract->with('profile')->find(request()->user()->id);

        $html = file_get_contents(config('config.ocr_get_url'));

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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete()
    {
        if (count(request()->get('files')) > 0)
        {
            $files = request()->get('files');

            Artisan::call('ocrfile:delete', ['files' => $files]);

            Toastr::success('OCR deleted successfully.', 'OCR Delete');

            return redirect()->route('admin.ocr.index');
        }

        Toastr::error('Deleting did not work.', 'OCR Delete');

        return redirect()->route('admin.ocr.index');
    }
}
