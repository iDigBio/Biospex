<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

use App\Repositories\RepositoryInterface;

interface Expedition extends RepositoryInterface
{
    /**
     * Get Expedition for home page.
     *
     * @return mixed
     */
    public function getHomePageProjectExpedition();

    /**
     * Get expeditions for public page.
     *
     * @param null $sort
     * @param null $order
     * @param null $projectId
     * @return mixed
     */
    public function getExpeditionPublicIndex($sort = null, $order = null, $projectId = null);

    /**
     * Get expeditions for admin page.
     *
     * @param null $userId
     * @param null $sort
     * @param null $order
     * * @param null $projectId
     * @return mixed
     */
    public function getExpeditionAdminIndex($userId = null, $sort = null, $order = null, $projectId = null);

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
     * @param $projectId
     * @param $expeditionId
     * @return mixed
     */
    public function expeditionDownloadsByActor($projectId, $expeditionId);

    /**
     * Find expeditions for project with relationships.
     *
     * @param $projectId
     * @param array $with
     * @return mixed
     */
    public function findExpeditionsByProjectIdWith($projectId, array $with = []);

    /**
     * Get Expedition stats.
     *
     * @param array $expeditionIds
     * @param array $columns
     * @return Collection
     */
    public function getExpeditionStats(array $expeditionIds = [], array $columns = ['*']);

    /**
     * Get expeditions having panoptes project.
     *
     * @param int $expeditionId
     * @return mixed
     */
    public function getExpeditionsHavingPanoptesProjects($expeditionId);

    /**
     * @param $expeditionId
     * @return mixed
     */
    public function findExpeditionHavingWorkflowManager($expeditionId);

}
