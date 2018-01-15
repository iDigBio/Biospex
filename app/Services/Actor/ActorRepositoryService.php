<?php

namespace App\Services\Actor;

use App\Repositories\Interfaces\Actor;
use App\Repositories\Interfaces\Download;
use App\Repositories\Interfaces\Expedition;
use App\Repositories\Interfaces\Project;
use App\Repositories\Interfaces\ExportQueue;
use App\Repositories\Interfaces\Subject;

class ActorRepositoryService extends ActorServiceBase
{

    /**
     * @var Subject
     */
    private $subjectContract;

    /**
     * @var Actor
     */
    private $actorContract;

    /**
     * @var Expedition
     */
    public $expeditionContract;

    /**
     * @var Download
     */
    public $downloadContract;

    /**
     * @var ExportQueue
     */
    public $exportQueueContract;

    /**
     * ActorServiceRepositories constructor.
     *
     * @param Subject $subjectContract
     * @param Actor $actorContract
     * @param Expedition $expeditionContract
     * @param ExportQueue $exportQueueContract
     * @param Download $downloadContract
     */
    public function __construct(
        Subject $subjectContract,
        Actor $actorContract,
        Expedition $expeditionContract,
        ExportQueue $exportQueueContract,
        Download $downloadContract
    )
    {
        $this->subjectContract = $subjectContract;
        $this->actorContract = $actorContract;
        $this->expeditionContract = $expeditionContract;
        $this->exportQueueContract = $exportQueueContract;
        $this->downloadContract = $downloadContract;
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
     * Get subjects using expedition id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSubjectsByExpeditionId()
    {
        return $this->subjectContract->findSubjectsByExpeditionId($this->config->expedition->id);
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
        return $this->downloadContract->updateOrCreate($attributes, $values);
    }

    /**
     * Create ExportQueue.
     *
     * @param $attributes
     */
    public function firstOrCreateExportQueue($attributes)
    {
        $this->exportQueueContract->firstOrCreate($attributes);
        event('exportQueue.updated');
    }

    /**
     * Update ExportQueue record.
     *
     * @param $exportId
     * @param $attributes
     * @return mixed
     */
    public function updateExportQueue($attributes, $exportId)
    {
        $this->exportQueueContract->update($attributes, $exportId);
        event('exportQueue.updated');

    }

    /**
     * Delete staged queue.
     *
     * @param $exportId
     * @return mixed
     */
    public function deleteExportQueue($exportId)
    {
        event('exportQueue.updated');
        return $this->exportQueueContract->delete($exportId);
    }
}