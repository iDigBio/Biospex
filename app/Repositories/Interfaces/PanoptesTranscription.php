<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface PanoptesTranscription extends RepositoryInterface
{
    /**
     * Get total transcriptions.
     *
     * @return mixed
     */
    public function getTotalTranscriptions();

    /**
     * Get total contributor count.
     *
     * @return mixed
     */
    public function getContributorCount();

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

    // Not used methods

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
     * Get transcription count per dates for workflow.
     *
     * @param $workflowId
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function getTranscriptionCountPerDate($workflowId, $begin, $end);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getTranscribersTranscriptionCount($projectId);

    /**
     * @param $expeditionId
     * @param $timestamp
     * @return mixed
     */
    public function getTranscriptionForDashboardJob($expeditionId, $timestamp);
}
