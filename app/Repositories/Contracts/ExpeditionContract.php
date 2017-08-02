<?php

namespace App\Repositories\Contracts;

interface ExpeditionContract extends RepositoryContract, CacheableContract
{

    /**
     * @param array $attributes
     * @param bool $syncRelations
     * @return mixed
     */
    public function createExpedition(array $attributes = [], $syncRelations = false);

    /**
     * @param $id
     * @param array $attributes
     * @param bool $syncRelations
     * @return mixed
     */
    public function updateExpedition($id, array $attributes = [], $syncRelations = false);

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
}
