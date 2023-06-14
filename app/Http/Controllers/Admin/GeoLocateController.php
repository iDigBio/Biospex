<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\GeoLocateFormRepository;
use App\Repositories\ProjectRepository;
use App\Services\Csv\GeoLocateExportService;
use App\Services\Process\GeoLocateProcessService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GeoLocateController extends Controller
{
    /**
     * @var \App\Services\Process\GeoLocateProcessService
     */
    private GeoLocateProcessService $geoLocateProcessService;

    /**
     * @param \App\Services\Process\GeoLocateProcessService $geoLocateProcessService
     */
    public function __construct(GeoLocateProcessService $geoLocateProcessService)
    {
        $this->geoLocateProcessService = $geoLocateProcessService;
    }

    public function create(int $projectId, int $expeditionId)
    {
        try {
            $relations = ['project.group', 'stat'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return redirect()->route('admin.projects.index');
            }

            $data = $this->geoLocateProcessService->getForm($projectId, $expeditionId);

            return view('admin.geolocate.create', compact('expedition', 'data'));
        } catch (\Exception $e)
        {
            \Flash::error(t('Error %s.', $e->getMessage()));
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    public function store(int $projectId, int $expeditionId, GeoLocateExportService $service)
    {
        DB::beginTransaction();

        try {

            $fields = $this->geoLocateProcessService->mapExportFields(request()->all());

            $form = $this->geoLocateProcessService->saveForm($fields, $expeditionId);

            //$this->filePath = $this->geoLocateProcessService->getExportFilePath($fileName);

            $reservedColumns =config('config.reserved_columns.geolocate');

            $service->build($this->filePath, $fields, $reservedColumns);

            $downloadUrl = route('admin.download.export', ['file' => base64_encode($fileName)]);

            DB::commit();

            $this->user->notify(new ExportNotification($downloadUrl));

            return;

        } catch (Exception $exception) {
            $attributes = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];

            $this->user->notify(new JobErrorNotification($attributes));

            DB::rollback();

            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }
}
