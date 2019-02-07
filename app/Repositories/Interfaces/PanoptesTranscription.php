<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface PanoptesTranscription extends RepositoryInterface
{

    /**
     * Return unique transcriber count for project.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectTranscriberCount($projectId);

    /**
     * Return project transcription count.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectTranscriptionCount($projectId);

    /**
     * Return expedition transcription count.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function getExpeditionTranscriptionCount($expeditionId);


    // Not used methods

    /**
     * @param $expeditionId
     * @return mixed
     */
    public function getTranscriptionCountByExpeditionId($expeditionId);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getMinFinishedAtDateByProjectId($projectId);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getMaxFinishedAtDateByProjectId($projectId);

    /**
     * @param $workflowId
     * @return mixed
     */
    public function getTranscriptionCountPerDate($workflowId);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getUserTranscriptionCount($projectId);

    /**
     * @param $expeditionId
     * @param $timestamp
     * @return mixed
     */
    public function getTranscriptionForDashboardJob($expeditionId, $timestamp);
}
