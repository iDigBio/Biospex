<?php  

namespace App\Http\Controllers\Admin;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Import;
use App\Jobs\DwcFileImportJob;
use App\Jobs\DwcUriImportJob;
use App\Jobs\RecordsetImportJob;
use App\Repositories\Interfaces\Project;

class ImportsController extends Controller
{
    /**
     * Add data to project
     *
     * @param \App\Repositories\Interfaces\Project $projectContract
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function index(Project $projectContract, $projectId)
    {
        $project = $projectContract->find($projectId);

        return view('admin.partials.import-modal-body', compact('project'));
    }

    /**
     * Upload DWC file.
     *
     * @param \App\Repositories\Interfaces\Import $importContract
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dwcFile(Import $importContract)
    {
        try {

            $projectId = request()->input('project_id');
            $path = request()->file('dwc')->store('imports/subjects');

            $import = $importContract->create([
                'user_id'    => \Auth::user()->id,
                'project_id' => $projectId,
                'file'       => $path
            ]);

            DwcFileImportJob::dispatch($import);

            FlashHelper::success(__('messages.upload_import_success'));

            return redirect()->back();
        }
        catch(\Exception $e)
        {
            FlashHelper::error(__('messages.upload_import_error'));

            return redirect()->back();
        }
    }

    /**
     * Upload record set.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recordSet()
    {
        try
        {
            $projectId = request()->input('project_id');

            $data = [
                'id'         => request()->input('recordset'),
                'user_id'    => \Auth::user()->id,
                'project_id' => $projectId
            ];

            RecordsetImportJob::dispatch($data);

            FlashHelper::success(__('messages.upload_import_success'));

            return redirect()->back();
        }
        catch(\Exception $e)
        {
            FlashHelper::error(__('messages.upload_import_error'));

            return redirect()->back();
        }
    }

    /**
     * Upload Dwc uri.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dwcUri()
    {
        try
        {
            $projectId = request()->input('project_id');

            $data = [
                'id'      => $projectId,
                'user_id' => \Auth::user()->id,
                'url'     => request()->input('dwc-url')
            ];

            DwcUriImportJob::dispatch($data);

            FlashHelper::success(__('messages.upload_import_success'));

            return redirect()->back();
        }
        catch(\Exception $e)
        {
            FlashHelper::error(__('messages.upload_import_error'));

            return redirect()->back();
        }
    }
}
