<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\GeoLocateFormRepository;
use App\Repositories\ProjectRepository;
use App\Services\Process\GeoLocateProcessService;
use Illuminate\Http\Request;

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

    public function index(int $projectId, int $expeditionId)
    {
        $project = $this->geoLocateProcessService->findProjectWith($projectId, ['group']);
        if (! $this->checkPermissions('createProject', $project->group)) {
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        try {
            $data = $this->geoLocateProcessService->getForm($projectId, $expeditionId);

            return view('admin.geolocate.index', compact('data'));
        } catch (\Exception $e)
        {
            \Flash::error(t('Error %s.', $e->getMessage()));
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }
}
