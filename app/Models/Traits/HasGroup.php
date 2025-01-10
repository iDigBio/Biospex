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

namespace App\Models\Traits;

trait HasGroup
{
    /**
     * Assign the given group to the user.
     */
    public function assignGroup($group): \Illuminate\Database\Eloquent\Model
    {
        return $this->groups()->save($group);
    }

    /**
     * Detach Group.
     */
    public function detachGroup($groupId): int
    {
        return $this->groups()->detach($groupId);
    }

    /**
     * Determine if the user has the given group.
     *
     * @param  mixed  $group
     */
    public function hasGroup($group): bool
    {
        if (is_string($group)) {
            return $this->groups->contains('title', $group);
        }

        return (bool) $this->groups->intersect(collect([$group]))->count();
    }

    /**
     * Check if user is admin group.
     */
    public function isAdmin(): bool
    {
        return $this->hasGroup(config('config.admin.group'));
    }
}
