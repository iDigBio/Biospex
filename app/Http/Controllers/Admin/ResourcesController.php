<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Resource;
use Storage;

class ResourcesController extends Controller
{
    /**
     * @var Resource
     */
    private $resourceContract;

    /**
     * ResourcesController constructor.
     *
     * @param Resource $resourceContract
     */
    public function __construct(Resource $resourceContract)
    {
        $this->resourceContract = $resourceContract;
    }

    /**
     * Show categories.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $resources = $this->resourceContract->getResourcesOrdered();

        return view('frontend.resources.index', compact('resources'));
    }

    /**
     * Download resource file.
     *
     * @param $resourceId
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download($resourceId)
    {
        $download = $this->resourceContract->find($resourceId);
        $document = $download->document;

        if (! $document->exists() || ! file_exists(public_path('storage' . $document->path()))) {
            Flash::error('File cannot be found.');

            return redirect()->route('web.resources.index');
        }

        return Storage::download('public/' . $document->path());
    }
}
