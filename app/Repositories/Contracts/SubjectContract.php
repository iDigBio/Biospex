<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface SubjectContract extends RepositoryContract, CacheableContract
{

    /**
     * Find subjects by expedition id.
     *
     * @param $expeditionId
     * @param array $attributes
     * @return mixed
     */
    public function findSubjectsByExpeditionId($expeditionId, array $attributes = ['*']);

    /**
     * Get Unassigned count.
     *
     * @param $id
     * @return mixed
     */
    public function getUnassignedCount($id);

    /**
     * Get Subject ids.
     *
     * @param $projectId
     * @param null $take
     * @param null $expeditionId
     * @return mixed
     */
    public function getSubjectIds($projectId, $take = null, $expeditionId = null);

    /**
     * Detach subjects.
     *
     * @param array $ids
     * @param $expeditionId
     * @return mixed
     */
    public function detachSubjects($ids = [], $expeditionId);

    /**
     * Get total row count.
     *
     * @param array $vars
     * @return mixed
     */
    public function getTotalRowCount(array $vars = []);

    /**
     * Get rows.
     *
     * @param array $vars
     * @return mixed
     */
    public function getRows(array $vars = []);
}