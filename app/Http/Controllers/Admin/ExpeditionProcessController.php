<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkflowIdFormRequest;
use App\Jobs\OcrCreateJob;
use App\Jobs\PanoptesProjectUpdateJob;
use App\Repositories\ExpeditionRepository;
use App\Repositories\PanoptesProjectRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\WorkflowManagerRepository;
use Exception;
use Flash;
use Illuminate\Support\Facades\Auth;

class ExpeditionProcessController extends Controller
{
    /**
     * @var \App\Repositories\ProjectRepository
     */
    private ProjectRepository $projectRepository;

    /**
     * @var \App\Repositories\ExpeditionRepository
     */
    private ExpeditionRepository $expeditionRepository;

    /**
     * @var \App\Repositories\WorkflowManagerRepository
     */
    private WorkflowManagerRepository $workflowManagerRepository;

    /**
     * Construct
     *
     * @param \App\Repositories\ProjectRepository $projectRepository
     * @param \App\Repositories\ExpeditionRepository $expeditionRepository
     * @param \App\Repositories\WorkflowManagerRepository $workflowManagerRepository
     */
    public function __construct(
        ProjectRepository $projectRepository,
        ExpeditionRepository $expeditionRepository,
        WorkflowManagerRepository $workflowManagerRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->expeditionRepository = $expeditionRepository;
        $this->workflowManagerRepository = $workflowManagerRepository;
    }

    /**
     * Sort expedition admin page.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|null
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function sort()
    {
        if (! request()->ajax()) {
            return null;
        }

        $user = Auth::user();

        $type = request()->get('type');
        $sort = request()->get('sort');
        $order = request()->get('order');
        $projectId = request()->get('id');

        [
            $active,
            $completed,
        ] = $this->expeditionRepository->getExpeditionAdminIndex($user->id, $sort, $order, $projectId)->partition(function (
                $expedition
            ) {
                return $expedition->completed === 0;
            });

        $expeditions = $type === 'active' ? $active : $completed;

        return view('admin.expedition.partials.expedition', compact('expeditions'));
    }

    /**
     * Start processing expedition actors
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process($projectId, $expeditionId)
    {
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        try {
            $expedition = $this->expeditionRepository->findWith($expeditionId, [
                'workflow.actors',
                'panoptesProject',
                'workflowManager',
                'stat',
            ]);

            if (null === $expedition->panoptesProject) {
                throw new Exception(t('NfnPanoptes Workflow Id is missing. Please update the Expedition once Workflow Id is acquired.'));
            }

            if (null !== $expedition->workflowManager) {
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->save();
                $message = t('The expedition has been removed from the process queue.');
            } else {
                // TODO process starts the overnight scripts. can't have geolocate start yet.
                $expedition->workflow->actors->reject(function($actor){
                    return $actor->id == config('config.geoLocateActorId');
                })->each(function ($actor) use ($expedition) {
                    $sync = [
                        $actor->id => ['order' => $actor->pivot->order, 'state' => $actor->pivot->state === 1 ? 2 : $actor->pivot->state],
                    ];
                    $expedition->actors()->sync($sync, false);
                });

                $this->workflowManagerRepository->create(['expedition_id' => $expeditionId]);
                $message = t('The expedition has been added to the process queue.');
            }

            Flash::success($message);

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        } catch (Exception $e) {
            Flash::error(t('An error occurred when trying to process the expedition: %s', $e->getMessage()));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }
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
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        $workflow = $this->workflowManagerRepository->findBy('expedition_id', $expeditionId);

        if ($workflow === null) {
            Flash::error(t('Expedition has no processes at this time.'));

            return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
        }

        $workflow->stopped = 1;
        $this->workflowManagerRepository->update(['stopped' => 1], $workflow->id);
        Flash::success(t('Expedition process has been stopped locally. This does not stop any processing occurring on remote sites.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Reprocess OCR.
     *
     * @param $projectId
     * @param $expeditionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ocr($projectId, $expeditionId)
    {
        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return redirect()->route('admin.projects.index');
        }

        OcrCreateJob::dispatch($projectId, $expeditionId);

        Flash::success(t('OCR processing has been submitted. It may take some time before appearing in the Processes menu. You will be notified by email when the process is complete.'));

        return redirect()->route('admin.expeditions.show', [$projectId, $expeditionId]);
    }

    /**
     * Update or create the workflow id.
     *
     * @param \App\Http\Requests\WorkflowIdFormRequest $request
     * @param $projectId
     * @param $expeditionId
     * @param \App\Repositories\PanoptesProjectRepository $panoptesProjectRepository
     * @return \Illuminate\Http\JsonResponse
     */
    public function workflowId(WorkflowIdFormRequest $request, $projectId, $expeditionId, PanoptesProjectRepository $panoptesProjectRepository)
    {
        if (! request()->ajax()) {
            return response()->json(['code' => 400, 'message' => t('Request must be ajax.')]);
        }

        $project = $this->projectRepository->findWith($projectId, ['group']);

        if (! $this->checkPermissions('updateProject', $project->group)) {
            return response()->json(['code' => 401, 'message' => t('You are not authorized to update project.')]);
        }

        try {
            if ($request->filled('panoptes_workflow_id')) {
                $attributes = [
                    'project_id'    => $projectId,
                    'expedition_id' => $expeditionId,
                ];

                $values = [
                    'project_id'           => $project->id,
                    'expedition_id'        => $expeditionId,
                    'panoptes_workflow_id' => $request->get('panoptes_workflow_id'),
                ];

                $panoptesProject = $panoptesProjectRepository->updateOrCreate($attributes, $values);

                PanoptesProjectUpdateJob::dispatch($panoptesProject);

                return response()->json(['code' => 200, 'message' => 'Workflow id is updated.']);
            }

            throw new Exception(t('Could not update Panoptes Workflow Id.'));
        } catch (Exception $exception) {
            return response()->json(['code' => 401, 'message' => $exception->getMessage()]);
        }
    }
}
