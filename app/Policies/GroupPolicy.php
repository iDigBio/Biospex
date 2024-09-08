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

use App\Models\Group;
use App\Models\User;

/**
 * Class GroupPolicy
 */
class GroupPolicy
{
    /**
     * Allow admins.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Is group owner.
     */
    public function isOwner(User $user, Group $group): bool
    {
        dd($user);

        return $user->id === $group->user_id;
    }

    /**
     * Check if user can create group
     */
    public function create(User $user, Group $group): bool
    {
        return true;
    }

    /**
     * Check if user can store group
     */
    public function store(User $user, Group $group): bool
    {
        return true;
    }

    /**
     * Check if user can read group.
     */
    public function read(User $user, Group $group): ?true
    {
        return $user->hasGroup($group) ? true : null;
    }

    /**
     * Check if user can read project for this group.
     */
    public function readProject(User $user, Group $group): bool
    {
        return $user->hasGroup($group);
    }

    /**
     * Check if user can create project in group.
     */
    public function createProject(User $user, Group $group): bool
    {
        return $user->hasGroup($group);
    }

    /**
     * Check if user can create project in group.
     */
    public function updateProject(User $user, Group $group): bool
    {
        return $user->hasGroup($group);
    }
}
