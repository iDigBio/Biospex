<?php  

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Import\ImportServiceFactory;
use App\Repositories\Contracts\Project;

class ImportsController extends Controller
{

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var ImportServiceFactory
     */
    protected $importFactory;

    /**
     * @var Request
     */
    protected $request;


    /**
     * ImportsController constructor.
     * @param ImportServiceFactory $importFactory
     * @param Project $project
     * @param Request $request
     */
    public function __construct(
        ImportServiceFactory $importFactory,
        Project $project,
        Request $request
    ) {
        $this->project = $project;
        $this->importFactory = $importFactory;
        $this->request = $request;
    }

    /**
     * Add data to project
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function import($id)
    {
        $project = $this->project->findWith($id, ['group']);

        return view('frontend.projects.add', compact('project'));
    }

    /**
     * Upload data file
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload($id)
    {
        $obj = $this->importFactory->create($this->request->input('class'));
        if (! $obj) {
            session_flash_push('error', trans('pages.bad_type'));

            return redirect()->route('projects.get.import', [$id]);
        }

        $validate = $obj->import($id);

        if ( ! empty($validate)) {
            return redirect()->route('projects.get.import', [$id])->withErrors($validate);
        }

        session_flash_push('success', trans('pages.upload_trans_success'));

        return redirect()->route('projects.get.show', [$id]);
    }
}
