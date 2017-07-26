<?php

namespace App\Repositories\Contracts;

interface PanoptesTranscriptionContract extends RepositoryContract, CacheableContract
{

    public function getTranscriptionCountByWorkflowId($workflowId, array $attributes= ['*']);

    public function getTotalTranscriptions();

    public function getTranscriptionCountByExpeditionId($expeditionId, array $attributes = ['*']);

    public function getMinFinishedAtDateByProjectId($projectId);

    public function getMaxFinishedAtDateByProjectId($projectId);

    public function getTranscriptionCountPerDate($workflowId);

    public function getContributorCount();

    public function getUserTranscriptionCount($projectId);
}
