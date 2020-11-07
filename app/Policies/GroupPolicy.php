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

namespace App\Policies;

/**
 * Class GroupPolicy
 *
 * @package App\Policies
 */
class GroupPolicy
{
    /**
     * Allow admins.
     *
     * @param $user
     * @return bool|null
     */
    public function before($user)
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Is group owner.
     *
     * @param $user
     * @param $group
     * @return bool
     */
    public function isOwner($user, $group)
    {
        return $user->id === $group->user_id;
    }

    /**
     * Check if user can create group
     *
     * @param $user
     * @return bool
     */
    public function create($user)
    {
        return true;
    }

    /**
     * Check if user can store group
     *
     * @param $user
     * @return bool
     */
    public function store($user)
    {
        return true;
    }

    /**
     * Check if user can read group.
     *
     * @param $user
     * @param $group
     * @return bool|string
     */
    public function read($user, $group)
    {
        return $user->hasGroup($group) ? true : null;
    }

    /**
     * Check if user can read project for this group.
     *
     * @param $user
     * @param $group
     * @return bool|null
     */
    public function readProject($user, $group)
    {
        return $user->hasGroup($group);
    }

    /**
     * Check if user can create project in group.
     *
     * @param $user
     * @param $group
     * @return bool|null
     */
    public function createProject($user, $group)
    {
        return $user->hasGroup($group);
    }

    /**
     * Check if user can create project in group.
     *
     * @param $user
     * @param $group
     * @return bool|null
     */
    public function updateProject($user, $group)
    {
        return $user->hasGroup($group);
    }
}
