<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Resource as ResourceContract;

class ResourcesController extends Controller
{

    /**
     * @var ResourceContact
     */
    private $resource;

    /**
     * ResourcesController constructor.
     * @param ResourceContract $resource
     */
    public function __construct(ResourceContract $resource)
    {

        $this->resource = $resource;
    }

    /**
     * Show categories.
     * 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $resources = $this->resource->orderBy(['order' => 'asc'])->get();

        return view('frontend.resources.index', compact('resources'));
    }

    /**
     * Download resource file.
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        $download = $this->resource->find($id);
        $file= public_path('resources/' . $download->document);

        return response()->download($file, $download->document, ['Content-Type: application/pdf']);
    }
}
