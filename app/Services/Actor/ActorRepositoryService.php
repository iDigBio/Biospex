<?php

namespace App\Services\Actor;

use App\Models\Subject;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\Download;
use App\Repositories\Contracts\ExpeditionContract;
use App\Repositories\Contracts\StagedQueueContract;
use App\Repositories\Contracts\SubjectContract;

class ActorRepositoryService
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
     * @var Download
     */
    public $download;
    /**
     * @var StagedQueueContract
     */
    private $stagedQueueContract;


    /**
     * ActorServiceRepositories constructor.
     *
     * @param SubjectContract $subjectContract
     * @param ActorContract $actorContract
     * @param ExpeditionContract $expeditionContract
     * @param StagedQueueContract $stagedQueueContract
     * @param Download $download
     */
    public function __construct(
        SubjectContract $subjectContract,
        ActorContract $actorContract,
        ExpeditionContract $expeditionContract,
        StagedQueueContract $stagedQueueContract,
        Download $download
    )
    {
        $this->subjectContract = $subjectContract;
        $this->actorContract = $actorContract;
        $this->expeditionContract = $expeditionContract;
        $this->stagedQueueContract = $stagedQueueContract;
        $this->download = $download;
    }

    public function beginTransaction()
    {

    }



    /**
     * Get subjects using expedition id.
     *
     * @param $expeditionId
     * @param bool $cached
     * @return \Illuminate\Support\Collection
     */
    public function getSubjectsByExpeditionId($expeditionId, $cached = false)
    {
        $cached ?: $this->subjectContract->setCacheLifetime(0);

        return $this->subjectContract->findWhere(['expedition_ids', '=', $expeditionId]);
    }

    /**
     * Add download files to downloads table.
     *
     * @param $recordId
     * @param $actorId
     * @param array $files
     */
    public function createDownloads($recordId, $actorId, array $files)
    {
        foreach ($files as $file)
        {
            $attributes = [
                'expedition_id' => $recordId,
                'actor_id'      => $actorId,
                'file'          => pathinfo($file, PATHINFO_BASENAME),
            ];

            $values = [
                'expedition_id' => $recordId,
                'actor_id'      => $actorId,
                'file'          => pathinfo($file, PATHINFO_BASENAME),
            ];
            $this->createDownload($attributes, $values);
        }
    }

    /**
     * Created download.
     *
     * @param $attributes
     * @param $values
     * @return mixed
     */
    public function createDownload($attributes, $values)
    {
        return $this->download->updateOrCreate($attributes, $values);
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