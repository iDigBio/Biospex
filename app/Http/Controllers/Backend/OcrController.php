<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Interfaces\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use DOMDocument;

class OcrController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @param User $userContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(User $userContract)
    {
        $user = $userContract->findWith(request()->user()->id, ['profile']);

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

            Flash::success('OCR deleted successfully.');

            return redirect()->route('admin.ocr.index');
        }

        Flash::error('Deleting did not work.');

        return redirect()->route('admin.ocr.index');
    }
}
