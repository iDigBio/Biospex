<?php  namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Project;
use App\Http\Requests\ImportFormRequest;
use App\Jobs\ImportCreateJob;

class ImportsController extends Controller
{
    /**
     * Add data to project
     *
     * @param $id
     * @param Project $repository
     * @return \Illuminate\View\View
     */
    public function import($id, Project $repository)
    {
        $project = $repository->findWith($id, ['group']);
        return view('front.projects.add', compact('project'));
    }

    /**
     * Upload data file
     *
     * @param $id
     * @param ImportFormRequest $request
     * @param ImportServiceFactory $factory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function upload($id, ImportFormRequest $request)
    {
        $result = $this->dispatch(new ImportCreateJob($request));

        if (! $result) {
            session_flash_push('error', trans('pages.bad_type'));
            return redirect()->route('projects.import', [$id]);
        }

        session_flash_push('success', trans('pages.upload_trans_success'));
        return redirect()->route('projects.show', [$id]);
    }
}
