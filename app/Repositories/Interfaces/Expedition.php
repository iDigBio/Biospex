<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

use App\Repositories\RepositoryInterface;

interface Expedition extends RepositoryInterface
{

    /**
     * Retrieve expeditions for Notes From Nature classification process.
     *
     * @param array $expeditionIds
     * @param array $attributes
     * @return mixed
     */
    public function getExpeditionsForNfnClassificationProcess(array $expeditionIds = [], array $attributes = ['*']);

    /**
     * Get count of Expedition Subjects.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function getExpeditionSubjectCounts($expeditionId);

    /**
     * Get Expeditions Visible to user.
     *
     * @param $userId
     * @param array $relations
     * @return \Illuminate\Support\Collection|mixed
     */
    public function expeditionsByUserId($userId, array $relations =[]);

    /**
     * Retrieve expedition project, group, actors, and downloads.
     *
     * @param $expeditionId
     * @return mixed
     */
    public function expeditionDownloadsByActor($expeditionId);

    /**
     * Find expeditions for project with relationships. Find only trashed if needed.
     *
     * @param $projectId
     * @param array $with
     * @param bool $trashed
     * @return mixed
     */
    public function findExpeditionsByProjectIdWith($projectId, array $with = [], $trashed = false);

    /**
     * Get Expedition stats.
     *
     * @param array $expeditionIds
     * @param array $columns
     * @return Collection
     */
    public function getExpeditionStats(array $expeditionIds = [], array $columns = ['*']);

    /**
     * Get expeditions having nfnworkflows.
     *
     * @param int $expeditionId
     * @return mixed
     */
    public function getExpeditionsHavingNfnWorkflows($expeditionId);

    /**
     * @param $expeditionId
     * @return mixed
     */
    public function findExpeditionHavingWorkflowManager($expeditionId);
}
