<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GeoLocateExportJob;
use App\Services\Csv\GeoLocateExportService;
use App\Services\Process\GeoLocateProcessService;
use Exception;
use Flash;
use Illuminate\Support\Facades\Auth;

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

    /**
     * Index page and also used for ajax post to retrieve fields.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function index(int $projectId, int $expeditionId)
    {
        $relations = ['stat', 'geoLocateForm'];

        $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

        $route = isset($expedition->geoLocateForm) ?
            route('admin.geolocate.show', [$projectId, $expeditionId, $expedition->geoLocateForm->id]) :
            route('admin.geolocate.show', [$projectId, $expeditionId]);
        $isForm = isset($expedition->geoLocateForm);

        return view('admin.geolocate.index',  compact('expedition', 'route', 'isForm'));
    }

    /**
     * Show form if it exists or create new one.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function show(int $projectId, int $expeditionId)
    {
        try {
            $relations = ['project.group', 'stat'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $this->geoLocateProcessService->setSourceType($expedition, request()->get('frm'));
            $form = $this->geoLocateProcessService->getForm($expedition);
            [$expertFileExists, $expertReviewExists, $sourceType] = $this->geoLocateProcessService->getSourceType();

            return request()->ajax() ?
                view('admin.geolocate.partials.form-fields', compact('expedition', 'form', 'sourceType')) :
                view('admin.geolocate.show', compact('expedition', 'form', 'expertFileExists', 'expertReviewExists', 'sourceType'));
        } catch (\Exception $e)
        {
            Flash::error(t('Error %s.', $e->getMessage()));
            return redirect()->route('admin.expeditions.index', [$projectId, $expeditionId]);
        }
    }

    /**
     * Store form data.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @param \App\Services\Csv\GeoLocateExportService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(int $projectId, int $expeditionId, GeoLocateExportService $service)
    {

        try {
            $relations = ['project.group'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $fields = $this->geoLocateProcessService->cleanArray(request()->all());

            $this->geoLocateProcessService->saveForm($fields, $expeditionId);

            Flash::success(t('Form has been saved.'));

            return redirect()->route('admin.geolocate.index', [$projectId, $expeditionId]);

        } catch (Exception $exception) {
            Flash::error(t('Error %s.', $exception->getMessage()));
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Export form selections to csv.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export(int $projectId, int $expeditionId)
    {
        try {
            $relations = ['project.group', 'geoLocateForm'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            GeoLocateExportJob::dispatch($expedition->geoLocateForm, Auth::user());

            Flash::success(t('Geo Locate export job scheduled for processing. You will receive an email when file has been created.'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        } catch (Exception $exception) {
            Flash::error(t('Error %s.', $exception->getMessage()));
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }

    /**
     * Delete GeoLocateForm and associated data and file.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(int $projectId, int $expeditionId)
    {
        try {
            $relations = ['project.group', 'geoLocateForm'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $this->geoLocateProcessService->deleteGeoLocateFile($expedition->geoLocateForm->file_path);
            $this->geoLocateProcessService->deleteGeoLocate($expeditionId);
            $expedition->geoLocateForm->delete();

            Flash::success(t('GeoLocate form data and file deleted.'));
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);

        }catch (Exception $exception) {
            Flash::error(t('Error %s.', $exception->getMessage()));
            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

    }
}
