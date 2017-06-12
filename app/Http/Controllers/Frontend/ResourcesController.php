<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ResourceContract;

class ResourcesController extends Controller
{

    /**
     * @var ResourceContract
     */
    private $resourceContract;

    /**
     * ResourcesController constructor.
     * @param ResourceContract $resourceContract
     */
    public function __construct(ResourceContract $resourceContract)
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
        $resources = $this->resourceContract->orderBy('order', 'asc')->findAll();

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
        $download = $this->resourceContract->find($id);
        $file= public_path('resources/' . $download->document);

        return response()->download($file, $download->document, ['Content-Type: application/pdf']);
    }
}
