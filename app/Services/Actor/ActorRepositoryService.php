<?php

namespace App\Services\Actor;

use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\DownloadContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\ExportQueueContract;
use App\Repositories\Contracts\SubjectContract;

class ActorRepositoryService extends ActorServiceBase
{

    /**
     * @var SubjectContract
     */
    private $subjectContract;

    /**
     * @var ActorContract
     */
    private $actorContract;

    /**
     * @var ExpeditionContract
     */
    public $expeditionContract;

    /**
     * @var DownloadContract
     */
    public $downloadContract;

    /**
     * @var ExportQueueContract
     */
    public $exportQueueContract;

    /**
     * @var ProjectContract
     */
    public $projectContract;


    /**
     * ActorServiceRepositories constructor.
     *
     * @param SubjectContract $subjectContract
     * @param ActorContract $actorContract
     * @param ExpeditionContract $expeditionContract
     * @param ExportQueueContract $exportQueueContract
     * @param DownloadContract $downloadContract
     * @param ProjectContract $projectContract
     */
    public function __construct(
        SubjectContract $subjectContract,
        ActorContract $actorContract,
        ExpeditionContract $expeditionContract,
        ExportQueueContract $exportQueueContract,
        DownloadContract $downloadContract,
        ProjectContract $projectContract
    )
    {
        $this->subjectContract = $subjectContract;
        $this->actorContract = $actorContract;
        $this->expeditionContract = $expeditionContract;
        $this->exportQueueContract = $exportQueueContract;
        $this->downloadContract = $downloadContract;
        $this->projectContract = $projectContract;
    }

    /**
     * Set actor service configuration.
     *
     * @param ActorServiceConfig $config
     */
    public function setActorServiceConfig(ActorServiceConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Get the project and group using project id.
     *
     * @return mixed
     */
    public function getProjectGroupById()
    {
        return $this->projectContract->setCacheLifetime(0)
            ->with('group')
            ->find($this->config->expedition->project_id);
    }

    /**
     * Get subjects using expedition id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSubjectsByExpeditionId()
    {
        return $this->subjectContract->setCacheLifetime(0)
            ->findSubjectsByExpeditionId($this->config->expedition->id);
    }

    /**
     * Created download.
     *
     * @param $attributes
     * @param $values
     * @return mixed
     */
    public function updateOrCreateDownload($attributes, $values)
    {
        return $this->downloadContract->updateOrCreateDownload($attributes, $values);
    }

    /**
     * Create ExportQueue.
     *
     * @param $attributes
     * @return mixed
     */
    public function firstOrCreateExportQueue($attributes)
    {
        return $this->exportQueueContract->firstOrCreateExportQueue($attributes);
    }

    /**
     * Update ExportQueue record.
     *
     * @param $id
     * @param $attributes
     * @return mixed
     */
    public function updateExportQueue($id, $attributes)
    {
        return $this->exportQueueContract->update($id, $attributes);
    }

    /**
     * Delete staged queue.
     *
     * @param $id
     * @return mixed
     */
    public function deleteExportQueue($id)
    {
        return $this->exportQueueContract->delete($id);
    }
}