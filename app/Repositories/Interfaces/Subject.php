<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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

    /**
     * Delete all unassigned subjects from project.
     *
     * @param $projectId
     * @return mixed
     */
    public function deleteUnassignedSubjects($projectId);
}