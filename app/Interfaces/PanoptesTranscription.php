<?php

namespace App\Interfaces;

interface PanoptesTranscription extends Eloquent
{

    /**
     * @return mixed
     */
    public function getTotalTranscriptions();

    /**
     * @param $expeditionId
     * @param array $attributes
     * @return mixed
     */
    public function getTranscriptionCountByExpeditionId($expeditionId, array $attributes = ['*']);

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
     * @return mixed
     */
    public function getContributorCount();

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
