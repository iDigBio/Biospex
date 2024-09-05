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
     * 
     * @param $group
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function assignGroup($group)
    {
        return $this->groups()->save($group);
    }

    /**
     * Detach Group.
     *
     * @param $groupId
     * @return int
     */
    public function detachGroup($groupId)
    {
        return $this->groups()->detach($groupId);
    }

    /**
     * Determine if the user has the given group.
     *
     * @param  mixed $group
     * @return boolean
     */
    public function hasGroup($group)
    {
        if (is_string($group)) {
            return $this->groups->contains('title', $group);
        }

        return !! $this->groups->intersect(collect([$group]))->count();
    }

    /**
     * Check if user is admin group.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasGroup(config('config.admin.group'));
    }

}