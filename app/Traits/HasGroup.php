<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Traits;

/**
 * Trait HasGroup
 *
 * Provides group management functionality for Eloquent models. This trait
 * enables models to have relationships with groups and provides methods
 * for assigning, detaching, and checking group membership. It's commonly
 * used with User models to implement role-based access control.
 *
 * Key Features:
 * - Group assignment and detachment functionality
 * - Group membership checking with flexible input types
 * - Admin group verification based on configuration
 * - Support for both string and object group identification
 *
 * Requirements:
 * The model using this trait must have a 'groups' relationship defined
 * (typically a many-to-many relationship with the Group model).
 */
trait HasGroup
{
    /**
     * Assign the given group to the model.
     *
     * This method creates a relationship between the current model and the
     * specified group by saving the group through the groups relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $group  The group model to assign
     * @return \Illuminate\Database\Eloquent\Model The saved group model
     */
    public function assignGroup($group): \Illuminate\Database\Eloquent\Model
    {
        return $this->groups()->save($group);
    }

    /**
     * Detach a group from the model.
     *
     * This method removes the relationship between the current model and the
     * specified group by detaching it from the groups relationship.
     *
     * @param  mixed  $groupId  The ID of the group to detach
     * @return int The number of records affected (typically 1 if successful)
     */
    public function detachGroup($groupId): int
    {
        return $this->groups()->detach($groupId);
    }

    /**
     * Determine if the model has the given group.
     *
     * This method checks if the current model belongs to the specified group.
     * It supports both string-based group identification (by title) and
     * object-based group identification (by model instance).
     *
     * @param  mixed  $group  The group to check (string title or Group model)
     * @return bool True if the model has the group, false otherwise
     */
    public function hasGroup($group): bool
    {
        if (is_string($group)) {
            return $this->groups->contains('title', $group);
        }

        return (bool) $this->groups->intersect(collect([$group]))->count();
    }

    /**
     * Check if the model belongs to the admin group.
     *
     * This method determines if the current model is a member of the
     * administrative group as defined in the application configuration.
     *
     * @return bool True if the model is an admin, false otherwise
     */
    public function isAdmin(): bool
    {
        return $this->hasGroup(config('config.admin.group'));
    }
}
