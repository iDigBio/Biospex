<?php

namespace App\Http\Controllers\Front;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Resource;
use Storage;

class ResourcesController extends Controller
{
    /**
     * Show resources.
     *
     * @param \App\Repositories\Interfaces\Resource $resourceContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Resource $resourceContract)
    {
        $resources = $resourceContract->getResourcesOrdered();

        return view('front.resource.index', compact('resources'));
    }

    /**
     * Download resource file.
     *
     * @param \App\Repositories\Interfaces\Resource $resourceContract
     * @param $resourceId
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Resource $resourceContract, $resourceId)
    {
        $download = $resourceContract->find($resourceId);
        $document = $download->document;

        if (! $document->exists() || ! file_exists(public_path('storage' . $document->path()))) {
            FlashHelper::error('File cannot be found.');

            return redirect()->route('front.resource.index');
        }

        return Storage::download('public/' . $document->path());
    }
}
