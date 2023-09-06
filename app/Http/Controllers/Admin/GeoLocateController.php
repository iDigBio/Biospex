<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GeoLocateExportJob;
use App\Services\Process\GeoLocateProcessService;
use Exception;
use Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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
     * @return \Illuminate\Contracts\View\View
     */
    public function index(int $projectId, int $expeditionId): \Illuminate\Contracts\View\View
    {
        $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId);

        $route = route('admin.geolocate.show', [$projectId, $expeditionId]);
        $isForm = isset($expedition->geo_locate_form_id);

        return view('admin.geolocate.index', compact('expedition', 'route', 'isForm'));
    }

    /**
     * Show form if it exists or create new one.
     *
     * @param int $projectId
     * @param int $expeditionId
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(int $projectId, int $expeditionId): View|RedirectResponse
    {
        try {
            $relations = ['project.group.geoLocateForms'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $route = route('admin.geolocate.form', [$expedition->project->id, $expedition->id]);

            return view('admin.geolocate.show', compact('expedition', 'route'));

        } catch (\Exception $e) {
            Flash::error(t('Error %s.', $e->getMessage()));

            return redirect()->route('admin.expeditions.index', [$projectId, $expeditionId]);
        }
    }

    /**
     * Display form.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|string[]
     */
    public function form(int $projectId, int $expeditionId)
    {
        if (! request()->ajax()) {
            return ['Error: Ajax requests only'];
        }

        try {
            $relations = ['project.group.geoLocateForms'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('readProject', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $form = $this->geoLocateProcessService->getForm($expedition, request()->all());

            return view('admin.geolocate.partials.form-fields', compact('expedition', 'form'));

        } catch (\Exception $e) {
            Flash::error(t('Error %s.', $e->getMessage()));

            return redirect()->route('admin.expeditions.index', [$projectId, $expeditionId]);
        }
    }

    /**
     * Store form data.
     *
     * @param int $projectId
     * @param int $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(int $projectId, int $expeditionId)
    {

        try {
            $relations = ['project.group'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $this->geoLocateProcessService->saveForm(request()->all(), $expedition);

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
    public function export(int $projectId, int $expeditionId): RedirectResponse
    {
        try {
            $relations = ['project.group'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('updateProject', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            GeoLocateExportJob::dispatch($expedition, Auth::user());

            Flash::success(t('Geo Locate export job scheduled for processing. You will receive an email when file has been created.'));

            return redirect()->route('admin.geolocate.index', [$projectId, $expeditionId]);
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
    public function delete(int $projectId, int $expeditionId): RedirectResponse
    {
        try {
            $relations = ['project.group'];

            $expedition = $this->geoLocateProcessService->findExpeditionWithRelations($expeditionId, $relations);

            if (! $this->checkPermissions('isOwner', $expedition->project->group)) {
                return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
            }

            $this->geoLocateProcessService->deleteGeoLocateFile($expeditionId);
            $this->geoLocateProcessService->deleteGeoLocate($expeditionId);

            $expedition->geoLocateForm()->dissociate()->save();

            Flash::success(t('GeoLocate form data and file deleted.'));

            return redirect()->route('admin.geolocate.index', [$projectId, $expeditionId]);
        } catch (Exception $exception) {
            Flash::error(t('Error %s.', $exception->getMessage()));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
    }
}
