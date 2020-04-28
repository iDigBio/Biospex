<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface Subject extends RepositoryInterface
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
     * Get subjects by project and occurrence id.
     *
     * @param $projectId
     * @param $occurrenceId
     * @return mixed
     */
    public function getSubjectsByProjectOccurrence($projectId, $occurrenceId);

    /**
     * Get Unassigned count.
     *
     * @param $projectId
     * @return mixed
     */
    public function getUnassignedCount($projectId);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getSubjectAssignedCount($projectId);

    /**
     * Detach subjects.
     *
     * @param array $subjects
     * @param $expeditionId
     * @return mixed
     */
    public function detachSubjects($subjects, $expeditionId);

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

    /**
     * Find by access uri.
     *
     * @param $accessURI
     * @return mixed
     */
    public function findByAccessUri($accessURI);
}