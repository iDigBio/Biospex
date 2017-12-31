<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface Expedition extends Eloquent
{

    /**
     * Retrieve expeditions for Notes From Nature classification process.
     *
     * @param array $ids
     * @param array $attributes
     * @return mixed
     */
    public function getExpeditionsForNfnClassificationProcess(array $ids = [], array $attributes = ['*']);

    /**
     * Get count of Expedition Subjects.
     *
     * @param $id
     * @return mixed
     */
    public function getExpeditionSubjectCounts($id);

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
     * @param array $ids
     * @param array $columns
     * @return Collection
     */
    public function getExpeditionStats(array $ids = [], array $columns = ['*']);

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
