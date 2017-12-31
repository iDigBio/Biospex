<?php

namespace App\Services\Model;

use App\Facades\Flash;
use App\Interfaces\Project;
use App\Jobs\UpdateNfnWorkflowJob;
use App\Interfaces\Expedition;
use App\Interfaces\ExpeditionStat;
use App\Interfaces\NfnWorkflow;
use App\Interfaces\Subject;
use App\Interfaces\WorkflowManager;
use App\Services\File\FileService;
use Artisan;
use Illuminate\Foundation\Bus\DispatchesJobs;
use JavaScript;

class ExpeditionService
{

    use DispatchesJobs;

    /**
     * @var Expedition
     */
    private $expeditionContract;

    /**
     * @var NfnWorkflow
     */
    private $nfnWorkflowContract;

    /**
     * @var ExpeditionStat
     */
    private $expeditionStatContract;

    /**
     * @var Subject
     */
    private $subjectContract;

    /**
     * @var WorkflowManager
     */
    private $workflowManagerContract;

    /**
     * @var Project
     */
    private $projectContract;

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var OcrQueueService
     */
    private $ocrQueueService;

    /**
     * ExpeditionService constructor.
     * @param Expedition $expeditionContract
     * @param Project $projectContract
     * @param NfnWorkflow $nfnWorkflowContract
     * @param ExpeditionStat $expeditionStatContract
     * @param Subject $subjectContract
     * @param WorkflowManager $workflowManagerContract
     * @param FileService $fileService
     * @param OcrQueueService $ocrQueueService
     */
    public function __construct(
        Expedition $expeditionContract,
        Project $projectContract,
        NfnWorkflow $nfnWorkflowContract,
        ExpeditionStat $expeditionStatContract,
        Subject $subjectContract,
        WorkflowManager $workflowManagerContract,
        FileService $fileService,
        OcrQueueService $ocrQueueService
    )
    {
        $this->expeditionContract = $expeditionContract;
        $this->nfnWorkflowContract = $nfnWorkflowContract;
        $this->expeditionStatContract = $expeditionStatContract;
        $this->subjectContract = $subjectContract;
        $this->workflowManagerContract = $workflowManagerContract;
        $this->projectContract = $projectContract;
        $this->fileService = $fileService;
        $this->ocrQueueService = $ocrQueueService;
    }

    /**
     * Get all expeditions.
     *
     * @return mixed
     */
    public function getAllExpeditions()
    {
        return $this->expeditionContract->all();
    }

    /**
     * Get all trashed expeditions.
     *
     * @return mixed
     */
    public function getOnlyTrashedExpeditions()
    {
        return $this->expeditionContract->getOnlyTrashed();
    }

    /**
     * Find expedition with relations.
     *
     * @param $id
     * @param array $with
     */
    public function findExpeditionWith($id, array $with = [])
    {
        return $this->expeditionContract->findWith($id, $with);
    }

    /**
     * Expeditions by user id.
     *
     * @param $userId
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getExpeditionsByUserId($userId)
    {
        $relations = ['stat', 'downloads', 'actors', 'project.group'];

        return $this->expeditionContract->expeditionsByUserId($userId, $relations);
    }

    /**
     * Get expedition for show page.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function getShowExpedition($expeditionId)
    {
        $relations = [
            'project.group',
            'project.ocrQueue',
            'downloads',
            'workflowManager',
            'stat'
        ];

        $expedition = $this->findExpeditionWith($expeditionId, $relations);

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('web.grids.show', [$expedition->project->id, $expedition->id]),
            'exportUrl'    => route('web.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'showCheckbox' => false,
            'explore'      => false
        ]);

        return $expedition;
    }

    /**
     * Get expedition for creating duplicate.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function getDuplicateCreateExpedition($expeditionId)
    {
        $expedition = $this->findExpeditionWith($expeditionId, ['project.group.permissions']);

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => 0,
            'subjectIds'   => [],
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('web.grids.create', [$expedition->project->id]),
            'exportUrl'    => route('web.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'showCheckbox' => true,
            'explore'      => false
        ]);

        return $expedition;
    }

    /**
     * Create expedition.
     *
     * @param array $attributes
     * @return mixed
     */
    public function createExpedition($attributes)
    {
        $expedition = $this->expeditionContract->create($attributes);

        $subjects = explode(',', $attributes['subjectIds']);
        $expedition->subjects()->sync($subjects);

        $values = [
            'subject_count'        => count($subjects),
            'transcriptions_total' => transcriptions_total(count($subjects)),
        ];

        $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);

        return $expedition;
    }

    /**
     * Create expedition from admin area.
     *
     * @param $attributes
     * @return mixed
     */
    public function createAdminExpedition($attributes)
    {
        return $this->expeditionContract->create($attributes);
    }

    /**
     * Get expedition for editing.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function getEditExpedition($expeditionId)
    {
        $relations = [
            'project.group.permissions',
            'workflowManager',
            'subjects',
            'nfnWorkflow'
        ];

        $expedition = $this->findExpeditionWith($expeditionId, $relations);

        $subjectIds = [];
        foreach ($expedition->subjects as $subject)
        {
            $subjectIds[] = $subject->_id;
        }

        JavaScript::put([
            'projectId'    => $expedition->project->id,
            'expeditionId' => $expedition->id,
            'subjectIds'   => $subjectIds,
            'maxSubjects'  => config('config.expedition_size'),
            'url'          => route('web.grids.edit', [$expedition->project->id, $expedition->id]),
            'exportUrl'    => route('web.grids.expedition.export', [$expedition->project->id, $expedition->id]),
            'showCheckbox' => $expedition->workflowManager === null,
            'explore'      => false
        ]);

        return $expedition;
    }

    /**
     * Update Expedition.
     *
     * @param $attributes
     * @param $expeditionId
     * @return bool
     */
    public function updateExpedition($attributes, $expeditionId)
    {
        try
        {
            $expedition = $this->expeditionContract->update($attributes, $expeditionId);

            if (isset($attributes['workflow']))
            {
                $values = [
                    'project_id'    => $attributes['project_id'],
                    'expedition_id' => $expedition->id,
                    'workflow'      => $attributes['workflow']
                ];

                $nfnWorkflow = $this->nfnWorkflowContract->updateOrCreate(['expedition_id' => $expedition->id], $values);

                $this->dispatch((new UpdateNfnWorkflowJob($nfnWorkflow))->onQueue(config('config.beanstalkd.workflow')));
            }

            // If process already in place, do not update subjects.
            $workflowManager = $this->workflowManagerContract->findBy('expedition_id', $expedition->id);
            if ($workflowManager !== null)
            {
                return true;
            }

            $expedition->load('subjects');
            $subjectIds = explode(',', $attributes['subjectIds']);
            $this->subjectContract->detachSubjects($expedition->subjects, $expedition->id);
            $expedition->subjects()->attach($subjectIds);

            $total = transcriptions_total(count($subjectIds));
            $completed = transcriptions_completed($expedition->id);
            $values = [
                'subject_count'            => count($subjectIds),
                'transcriptions_total'     => $total,
                'transcriptions_completed' => $completed,
                'percent_completed'        => transcriptions_percent_completed($total, $completed)
            ];

            $this->expeditionStatContract->updateOrCreate(['expedition_id' => $expedition->id], $values);

            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * Update expedition from admin area.
     *
     * @param $attributes
     * @param $expeditionId
     * @return mixed
     */
    public function updateAdminExpedition($attributes, $expeditionId)
    {
        return $this->expeditionContract->update($attributes, $expeditionId);
    }

    /**
     * Process the expedition.
     *
     * @param $expeditionId
     */
    public function processExpedition($expeditionId)
    {
        try
        {
            $expedition = $this->findExpeditionWith($expeditionId, ['project.workflow.actors', 'workflowManager']);

            if (null !== $expedition->workflowManager)
            {
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->save();
            }
            else
            {
                $expedition->project->workflow->actors->reject(function ($actor) {
                    return $actor->private;
                })->each(function ($actor) use ($expedition) {
                    $expedition->actors()->syncWithoutDetaching([$actor->id => ['order' => $actor->pivot->order]]);
                });

                $this->workflowManagerContract->create(['expedition_id' => $expeditionId]);
            }

            Artisan::call('workflow:manage', ['expedition' => $expeditionId]);

            Flash::success(trans('expeditions.expedition_process_success'));
        }
        catch (\Exception $e)
        {
            Flash::error(trans('expeditions.expedition_process_error', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Get project group and permissions for expedition.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectGroup($projectId)
    {
        return $this->projectContract->findWith($projectId, ['group.permissions']);
    }

    /**
     * Process ocr for expedition.
     *
     * @param $projectId
     * @param null $expeditionId
     */
    public function processOcr($projectId, $expeditionId = null)
    {
        $this->ocrQueueService->processOcr($projectId, $expeditionId) ?
            Flash::success(trans('expeditions.ocr_process_success')) :
            Flash::warning(trans('expeditions.ocr_process_error'));
    }

    /**
     * Toggle the workflow manager for an expeditions.
     *
     * @param $expeditionId
     */
    public function toggleExpeditionWorkflow($expeditionId)
    {
        $workflow = $this->workflowManagerContract->findBy('expedition_id', $expeditionId);

        if ($workflow === null)
        {
            Flash::error(trans('expeditions.process_no_exists'));

            return;
        }

        $workflow->stopped = 1;
        $this->workflowManagerContract->update(['stopped' => 1], $workflow->id);
        Flash::success(trans('expeditions.process_stopped'));

        return;
    }

    /**
     * Delete expedition.
     *
     * @param $expeditionId
     * @return bool
     */
    public function deleteExpedition($expeditionId)
    {
        try
        {
            $expedition = $this->findExpeditionWith($expeditionId, ['nfnWorkflow']);

            if (isset($record->nfnWorkflow))
            {
                Flash::error(trans('expeditions.expedition_process_exists'));

                return false;
            }

            $subjects = $this->subjectContract->findSubjectsByExpeditionId($expedition->id);

            if ($subjects->isNotEmpty())
            {
                $this->subjectContract->detachSubjects($subjects, $expedition->id);
            }

            $values = [
                'subject_count'            => 0,
                'transcriptions_total'     => 0,
                'transcriptions_completed' => 0,
                'percent_completed'        => 0.00
            ];

            $expedition->stat()->updateOrCreate(['expedition_id' => $expedition->id], $values);

            $this->expeditionContract->delete($expedition);

            Flash::success(trans('expeditions.expedition_deleted'));

            return true;
        }
        catch (\Exception $e)
        {
            Flash::error(trans('expeditions.expedition_delete_error'));

            return false;
        }
    }

    /**
     * Destory expedition.
     *
     * @param $expeditionId
     * @return bool
     */
    public function destroyExpedition($expeditionId)
    {
        try
        {
            $expedition = $this->expeditionContract->findOnlyTrashed($expeditionId, ['downloads']);

            $expedition->downloads->each(function($download){
                $this->fileService->filesystem->delete(config('config.nfn_export_dir') . '/' . $download->file);
            });

            $this->expeditionContract->destroy($expedition);

            Flash::success(trans('expeditions.expedition_destroyed'));

            return true;
        }
        catch (\Exception $e)
        {
            Flash::error(trans('expeditions.expedition_destroy_error'));

            return false;
        }
    }

    /**
     * Restore deleted expedition.
     *
     * @param $expeditionId
     */
    public function restoreExpedition($expeditionId)
    {
        $this->expeditionContract->restore($expeditionId) ?
            Flash::success(trans('expeditions.expedition_restore')) :
            Flash::error(trans('expeditions.expedition_restore_error'));

        return;
    }
}