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

interface Event extends RepositoryInterface
{
    /**
     * Get Events public page.
     *
     * @param null $sort
     * @param null $order
     * @param null $projectId
     * @return mixed
     */
    public function getEventPublicIndex($sort = null, $order = null, $projectId = null);

    /**
     * Get events for admin section by user id.
     * @param \App\Models\User $user
     * @param null $sort
     * @param null $order
     * @return mixed
     */
    public function getEventAdminIndex(\App\Models\User $user, $sort = null, $order = null);

    /**
     * @param array $attributes
     * @return mixed
     */
    public function createEvent(array $attributes);

    /**
     * @param array $attributes
     * @param $resourceId
     * @return mixed
     */
    public function updateEvent(array $attributes, $resourceId);

    /**
     * @param $userId
     * @return mixed
     */
    public function getUserEvents($userId);

    /**
     * @param $eventId
     * @return mixed
     */
    public function getEventShow($eventId);

    /**
     * @param $projectId
     * @param $user
     * @return mixed
     */
    public function checkEventExistsForClassificationUser($projectId, $user);

    /**
     * @param $projectId
     * @return mixed
     */
    public function getEventsByProjectId($projectId);

    /**
     * @param $eventId
     * @param array $columns
     * @return mixed
     */
    public function getEventScoreboard($eventId, array $columns = ['*']);
}