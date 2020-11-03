<?php
/*
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

    /**
     * Return expedition transcription count.
     *
     * @param int $expeditionId
     * @return mixed
     */
    public function getExpeditionTranscriptionCount(int $expeditionId);

    /**
     * Get min finished at date for project.
     *
     * @param $projectId
     * @return mixed
     */
    public function getMinFinishedAtDateByProjectId($projectId);

    /**
     * Get max finished date for project.
     *
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
     * Get Transcribers count.
     *
     * @param $projectId
     * @return mixed
     */
    public function getTranscribersTranscriptionCount($projectId);

    /**
     * Get transcriptions for adding to pusher_transcriptions.
     *
     * @param $expeditionId
     * @param null $timestamp
     * @return mixed
     */
    public function getTranscriptionForDashboardJob($expeditionId, $timestamp = null);
}
