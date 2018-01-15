<?php 

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Resource;

class ResourcesController extends Controller
{

    /**
     * @var Resource
     */
    private $resourceContract;

    /**
     * ResourcesController constructor.
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
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($resourceId)
    {
        $download = $this->resourceContract->find($resourceId);
        $file = public_path('resources/' . $download->document);
        if ( ! file_exists($file))
        {
            Flash::error('File cannot be found.');

            return redirect()->route('web.resources.index');
        }

        return response()->download($file, $download->document, ['Content-Type: application/pdf']);
    }
}
