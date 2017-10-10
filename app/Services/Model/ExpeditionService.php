<?php

namespace App\Services\Model;

use App\Exceptions\BiospexException;
use App\Exceptions\Handler;
use App\Jobs\UpdateNfnWorkflowJob;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ExpeditionStatContract;
use App\Repositories\Contracts\NfnWorkflowContract;
use App\Repositories\Contracts\SubjectContract;
use App\Repositories\Contracts\WorkflowManagerContract;
use Artisan;
use Illuminate\Foundation\Bus\DispatchesJobs;


class ExpeditionService
{
    use DispatchesJobs;

    /**
     * @var ExpeditionContract
     */
    private $expeditionContract;
    /**
     * @var NfnWorkflowContract
     */
    private $nfnWorkflowContract;
    /**
     * @var ExpeditionStatContract
     */
    private $expeditionStatContract;
    /**
     * @var SubjectContract
     */
    private $subjectContract;
    /**
     * @var WorkflowManagerContract
     */
    private $workflowManagerContract;
    /**
     * @var Handler
     */
    private $handler;

    /**
     * ExpeditionService constructor.
     * @param ExpeditionContract $expeditionContract
     * @param NfnWorkflowContract $nfnWorkflowContract
     * @param ExpeditionStatContract $expeditionStatContract
     * @param SubjectContract $subjectContract
     * @param WorkflowManagerContract $workflowManagerContract
     * @param Handler $handler
     */
    public function __construct(
        ExpeditionContract $expeditionContract,
        NfnWorkflowContract $nfnWorkflowContract,
        ExpeditionStatContract $expeditionStatContract,
        SubjectContract $subjectContract,
        WorkflowManagerContract $workflowManagerContract,
        Handler $handler
    )
    {
        $this->expeditionContract = $expeditionContract;
        $this->nfnWorkflowContract = $nfnWorkflowContract;
        $this->expeditionStatContract = $expeditionStatContract;
        $this->subjectContract = $subjectContract;
        $this->workflowManagerContract = $workflowManagerContract;
        $this->handler = $handler;
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

        return $this->expeditionContract->with($relations)->find($expeditionId);

    }

    /**
     * Get expedition for creating duplicate.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function getDuplicateCreateExpedition($expeditionId)
    {
        return $this->expeditionContract->with(['project.group.permissions'])->find($expeditionId);
    }

    /**
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

        return $this->expeditionContract->setCacheLifetime(0)->with($relations)->find($expeditionId);
    }

    /**
     * Update expedition.
     *
     * @param $expeditionId
     * @param $attributes
     * @return bool
     */
    public function updateExpedition($expeditionId, $attributes)
    {
        try
        {
            $expedition = $this->expeditionContract->update($expeditionId, $attributes);

            if ($attributes['workflow'] !== '')
            {
                $values = [
                    'project_id'    => $attributes['project_id'],
                    'expedition_id' => $expedition->id,
                    'workflow'      => $attributes['workflow']
                ];

                $nfnWorkflow = $this->nfnWorkflowContract->updateOrCreate(['expedition_id' => $expedition->id], $values);

                $this->dispatch((new UpdateNfnWorkflowJob($nfnWorkflow))->onQueue(config('config.beanstalkd.workflow')));
            }

            $existingSubjectIds = $expedition->subjects->pluck('_id');
            $subjectIds = explode(',', $attributes['subjectIds']);
            $workflowManager = $this->workflowManagerContract->findBy('expedition_id', $expedition->id);

            if (null === $workflowManager && ! $existingSubjectIds->isEmpty())
            {
                $this->subjectContract->detachSubjects($existingSubjectIds, $expedition->id);
                $expedition->subjects()->attach($subjectIds);
                \Cache::flush();
            }

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
        catch(\Exception $e)
        {
            return false;
        }
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
            $expedition = $this->expeditionContract->setCacheLifetime(0)
                ->with(['project.workflow.actors', 'workflowManager'])
                ->find($expeditionId);

            if (null !== $expedition->workflowManager)
            {
                $expedition->workflowManager->stopped = 0;
                $expedition->workflowManager->save();
            }
            else
            {
                $expedition->project->workflow->actors->reject(function($actor) {
                    return $actor->private;
                })->each(function($actor) use ($expedition){
                    $expedition->actors()->syncWithoutDetaching([$actor->id => ['order' => $actor->pivot->order]]);
                });

                $this->workflowManagerContract->create(['expedition_id' => $expeditionId]);
            }

            Artisan::call('workflow:manage', ['expedition' => $expeditionId]);

            session_flash_push('success', trans('expeditions.expedition_process_success'));
        }
        catch (BiospexException $e)
        {
            $this->handler->report($e);
            session_flash_push('error', trans('expeditions.expedition_process_error', ['error' => $e->getMessage()]));
        }
    }
}