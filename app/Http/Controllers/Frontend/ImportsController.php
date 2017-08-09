<?php  

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Import\ImportServiceFactory;
use App\Repositories\Contracts\ProjectContract;

class ImportsController extends Controller
{

    /**
     * @var ProjectContract
     */
    public $projectContract;

    /**
     * @var ImportServiceFactory
     */
    public $importFactory;

    /**
     * ImportsController constructor.
     * @param ImportServiceFactory $importFactory
     * @param ProjectContract $projectContract
     */
    public function __construct(
        ImportServiceFactory $importFactory,
        ProjectContract $projectContract
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
        $project = $this->projectContract->with('group')->find($id);

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
            session_flash_push('error', trans('pages.bad_type'));

            return redirect()->route('web.imports.import', [$id]);
        }

        $validate = $obj->import($id);

        if ( ! empty($validate)) {
            return redirect()->route('web.imports.import', [$id])->withErrors($validate);
        }

        session_flash_push('success', trans('pages.upload_trans_success'));

        return redirect()->route('web.projects.show', [$id]);
    }
}
