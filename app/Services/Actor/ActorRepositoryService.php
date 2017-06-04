<?php

namespace App\Services\Actor;

use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\DownloadContract;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\ProjectContract;
use App\Repositories\Contracts\StagedQueueContract;
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
     * @var StagedQueueContract
     */
    public $stagedQueueContract;

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
     * @param StagedQueueContract $stagedQueueContract
     * @param DownloadContract $downloadContract
     * @param ProjectContract $projectContract
     */
    public function __construct(
        SubjectContract $subjectContract,
        ActorContract $actorContract,
        ExpeditionContract $expeditionContract,
        StagedQueueContract $stagedQueueContract,
        DownloadContract $downloadContract,
        ProjectContract $projectContract
    )
    {
        $this->subjectContract = $subjectContract;
        $this->actorContract = $actorContract;
        $this->expeditionContract = $expeditionContract;
        $this->stagedQueueContract = $stagedQueueContract;
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
        $this->checkActorServiceConfig();

        return $this->projectContract->setCacheLifetime(0)
            ->findProjectWithRelations($this->config->expedition->project_id, ['group']);
    }

    /**
     * Get subjects using expedition id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSubjectsByExpeditionId()
    {
        $this->checkActorServiceConfig();

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
     * Create StagedQueue.
     *
     * @param $attributes
     * @return mixed
     */
    public function createStagedQueue($attributes)
    {
        return $this->stagedQueueContract->createStagedQueue($attributes);
    }

    /**
     * Update StagedQueue record.
     *
     * @param $id
     * @param $attributes
     * @return mixed
     */
    public function updateStagedQueue($id, $attributes)
    {
        return $this->stagedQueueContract->update($id, $attributes);
    }
}