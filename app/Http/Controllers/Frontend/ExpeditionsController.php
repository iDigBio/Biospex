<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpeditionFormRequest;
use App\Services\Model\ExpeditionService;
use Illuminate\Support\Facades\Auth;
use JavaScript;

class ExpeditionsController extends Controller
{
    /**
     * @var ExpeditionService
     */
    private $expeditionService;

    /**
     * ExpeditionsController constructor.
     *
     * @param ExpeditionService $expeditionService
     */
    public function __construct(
        ExpeditionService $expeditionService
    )
    {
        $this->expeditionService = $expeditionService;
    }

    /**
     * Display all expeditions for user.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $user->load('profile');
        $expeditions = $this->expeditionService->getExpeditionsByUserId($user->id);

        return view('frontend.expeditions.index', compact('expeditions', 'user'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $projectId
     * @return \Illuminate\View\View
     */
    public function create($projectId)
    {
        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('create', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        JavaScript::put([
            'projectId'    => $project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('webauth.grids.create', [$project->id]),
            'exportUrl'    => '',
            'showCheckbox' => true,
            'explore'      => false
        ]);

        return view('frontend.expeditions.create', compact('project'));
    }

    /**
     * Store new expedition.
     *
     * @param ExpeditionFormRequest $request
     * @param $projectId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ExpeditionFormRequest $request, $projectId)
    {
        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('create', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $expedition = $this->expeditionService->createExpedition($request->all());

        if ($expedition)
        {
            Flash::success(trans('expeditions.expedition_created'));

            return redirect()->route('webauth.expeditions.show', [$projectId, $expedition->id]);
        }

        Flash::error(trans('expeditions.expedition_save_error'));
        return redirect()->route('webauth.projects.show', [$projectId]);
    }

    /**
     * Display the specified resource
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($projectId, $expeditionId)
    {
        $expedition = $this->expeditionService->getShowExpedition($expeditionId);

        if ( ! $this->checkPermissions('read', $expedition->project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $btnDisable = ($expedition->project->ocrQueue->isEmpty() || $expedition->stat->subject_count === 0);

        return view('frontend.expeditions.show', compact('expedition', 'btnDisable'));
    }

    /**
     * Clone an existing expedition
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function duplicate($projectId, $expeditionId)
    {
        $expedition = $this->expeditionService->getDuplicateCreateExpedition($expeditionId);

        if ( ! $this->checkPermissions('create', $expedition->project))
        {
            return redirect()->route('webauth.projects.index');
        }

        return view('frontend.expeditions.clone', compact('expedition'));
    }

    /**
     * Show the form for editing the specified resource
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($projectId, $expeditionId)
    {
        $expedition = $this->expeditionService->getEditExpedition($expeditionId);

        if ( ! $this->checkPermissions('update', $expedition->project))
        {
            return redirect()->route('webauth.projects.index');
        }

        return view('frontend.expeditions.edit', compact('expedition'));
    }

    /**
     * Update expedition.
     *
     * @param ExpeditionFormRequest $request
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ExpeditionFormRequest $request, $projectId, $expeditionId)
    {
        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $result = $this->expeditionService->updateExpedition($request->all(), $expeditionId);

        if ( ! $result)
        {
            Flash::error(trans('expeditions.expedition_save_error'));

            return redirect()->route('webauth.expeditions.edit', [$projectId, $expeditionId]);
        }

        // Success!
        Flash::success(trans('expeditions.expedition_updated'));

        return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Start processing expedition actors
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process($projectId, $expeditionId)
    {
        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->expeditionService->processExpedition($expeditionId);

        return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Reprocess OCR.
     *
     * @param $projectId
     * @param $expeditionId
     * @return mixed
     */
    public function ocr($projectId, $expeditionId)
    {

        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->expeditionService->processOcr($project->id, $expeditionId);

        return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Stop a expedition process.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop($projectId, $expeditionId)
    {
        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('update', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->expeditionService->toggleExpeditionWorkflow($expeditionId);

        return redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($projectId, $expeditionId)
    {
        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('delete', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $result = $this->expeditionService->deleteExpedition($expeditionId);

        return $result ?
            redirect()->route('webauth.projects.show', [$projectId]) :
            redirect()->route('webauth.expeditions.show', [$projectId, $expeditionId]);

    }

    /**
     * Destroy the specified resource from storage.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($projectId, $expeditionId)
    {
        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('delete', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->expeditionService->destroyExpedition($expeditionId);

        return redirect()->route('webauth.projects.show', [$projectId]);
    }

    /**
     * Restore deleted expedition.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($projectId, $expeditionId)
    {
        $project = $this->expeditionService->getProjectGroup($projectId);

        if ( ! $this->checkPermissions('delete', $project))
        {
            return redirect()->route('webauth.projects.index');
        }

        $this->expeditionService->restoreExpedition($expeditionId);

        return redirect()->route('webauth.projects.show', [$projectId]);
    }
}
