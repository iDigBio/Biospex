<?php  

namespace App\Http\Controllers\Admin;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\DwcFileUpload;
use App\Http\Requests\DwcUriUpload;
use App\Http\Requests\RecordsetUpload;
use App\Repositories\Interfaces\Import;
use App\Jobs\DwcFileImportJob;
use App\Jobs\DwcUriImportJob;
use App\Jobs\RecordsetImportJob;
use App\Services\File\FileService;
use App\Repositories\Interfaces\Project;

class ImportsController extends Controller
{

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var Project
     */
    private $projectContract;

    /**
     * @var Import
     */
    private $importContract;

    /**
     * ImportsController constructor.
     * @param FileService $fileService
     * @param Project $projectContract
     * @param Import $importContract
     */
    public function __construct(
        FileService $fileService,
        Project $projectContract,
        Import $importContract
    )
    {
        $this->fileService = $fileService;
        $this->projectContract = $projectContract;
        $this->importContract = $importContract;
    }

    /**
     * Add data to project
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function import($projectId)
    {
        $project = $this->projectContract->findWith($projectId, ['group']);

        return view('frontend.projects.add', compact('project'));
    }

    /**
     * Upload DWC file.
     *
     * @param DwcFileUpload $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadDwcFile(DwcFileUpload $request, $projectId)
    {
        try {

            $path = $request->file('dwc')->store('imports/subjects');

            $import = $this->importContract->create([
                'user_id'    => $request->input('user_id'),
                'project_id' => $projectId,
                'file'       => $path
            ]);

            DwcFileImportJob::dispatch($import);

            Flash::success(trans('pages.upload_trans_success'));

            return redirect()->route('admin.projects.show', [$projectId]);
        }
        catch(\Exception $e)
        {
            Flash::error('Error uploading the file.');

            return redirect()->route('admin.projects.show', [$projectId]);
        }
    }

    /**
     * Upload record set.
     *
     * @param RecordsetUpload $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadRecordSet(RecordsetUpload $request, $projectId)
    {
        try
        {
            $data = [
                'id'         => $request->input('recordset'),
                'user_id'    => request()->input('user_id'),
                'project_id' => $projectId
            ];

            RecordsetImportJob::dispatch($data);

            Flash::success(trans('pages.upload_trans_success'));

            return redirect()->route('admin.projects.show', [$projectId]);
        }
        catch(\Exception $e)
        {
            Flash::error('Error uploading the file.');

            return redirect()->route('admin.projects.show', [$projectId]);
        }
    }

    /**
     * Upload Dwc uri.
     *
     * @param DwcUriUpload $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadDwcUri(DwcUriUpload $request, $projectId)
    {
        try
        {
            $data = [
                'id'      => $projectId,
                'user_id' => $request->input('user_id'),
                'url'     => $request->input('data-url')
            ];

            DwcUriImportJob::dispatch($data);

            Flash::success(trans('pages.upload_trans_success'));

            return redirect()->route('admin.projects.show', [$projectId]);
        }
        catch(\Exception $e)
        {
            Flash::error('Error uploading the file.');

            return redirect()->route('admin.projects.show', [$projectId]);
        }
    }
}
