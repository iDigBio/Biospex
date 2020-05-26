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

interface Project extends RepositoryInterface
{
    /**
     * Get list of projects for public project page.
     *
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getPublicProjectIndex($sort = null, $order = null);

    /**
     * Get list of projects for admin project page.
     *
     * @param $userId
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getAdminProjectIndex($userId, $sort = null, $order = null);

    /**
     * Get project show page by id with relationships.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectShow($projectId);

    /**
     * Get the project public page.
     *
     * @param $slug
     * @return mixed
     */
    public function getProjectPageBySlug($slug);

    /**
     * @param array $projectIds
     * @return mixed
     */
    public function getProjectsHavingTranscriptionLocations(array $projectIds = []);

    /**
     * @return mixed
     */
    public function getProjectEventSelect();

    /**
     * @param $projectId
     * @return mixed
     */
    public function getProjectForDelete($projectId);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getProjectForAmChartJob($projectId);

    /**
     * Get project for Darwin Core import.
     *
     * @param $projectId
     * @return mixed
     */
    public function getProjectForDarwinImportJob($projectId);

}