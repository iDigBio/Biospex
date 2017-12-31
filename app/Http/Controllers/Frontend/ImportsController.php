<?php  

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Services\Import\ImportServiceFactory;
use App\Interfaces\Project;

class ImportsController extends Controller
{

    /**
     * @var Project
     */
    public $projectContract;

    /**
     * @var ImportServiceFactory
     */
    public $importFactory;

    /**
     * ImportsController constructor.
     * @param ImportServiceFactory $importFactory
     * @param Project $projectContract
     */
    public function __construct(
        ImportServiceFactory $importFactory,
        Project $projectContract
    ) {
        $this->projectContract = $projectContract;
        $this->importFactory = $importFactory;
    }

    /**
     * Add data to project
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function import($id)
    {
        $project = $this->projectContract->findWith($id, ['group']);

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
        $obj = $this->importFactory->create(request()->input('class'));
        if (! $obj) {
            Flash::error(trans('pages.bad_type'));

            return redirect()->route('web.imports.import', [$id]);
        }

        $validate = $obj->import($id);

        if ( ! empty($validate)) {
            return redirect()->route('web.imports.import', [$id])->withErrors($validate);
        }

        Flash::success(trans('pages.upload_trans_success'));

        return redirect()->route('web.projects.show', [$id]);
    }
}
