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

interface Group extends RepositoryInterface
{



    /**
     * Get select list of groups for a user.
     *
     * @param $user
     * @return mixed
     */
    public function getUsersGroupsSelect($user);

    /**
     * Return user group ids.
     *
     * @param $userId
     * @return mixed
     */
    public function getUserGroupIds($userId);

    /**
     * Return groups by user id.
     *
     * @param $userId
     * @return mixed
     */
    public function getGroupsByUserId($userId);

    /**
     * Get data for group show page in admin section.
     *
     * @param $groupId
     * @return mixed
     * @throws \Exception
     */
    public function getGroupShow($groupId);

    /**
     * Get count of groups user belongs to.
     *
     * @param $userId
     * @return mixed
     */
    public function getUserGroupCount($userId);
}