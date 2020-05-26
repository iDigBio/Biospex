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

namespace App\Policies;

class BingoPolicy
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
     * Is owner of event.
     *
     * @param $user
     * @param $event
     * @return bool
     */
    public function isOwner($user, $event)
    {
        return $user->id === $event->owner_id;
    }

    /**
     * Check if user can create event
     * @param $user
     * @return bool
     */
    public function create($user)
    {
        return true;
    }

    /**
     * Check if user can store event
     * @param $user
     * @return bool
     */
    public function store($user)
    {
        return true;
    }

    /**
     * Check if user can read event.
     *
     * @param $user
     * @param $event
     * @return bool|string
     */
    public function read($user, $event)
    {
        return $this->isOwner($user, $event);
    }

    /**
     * Check if user can update event
     * @param $user
     * @param $event
     * @return mixed
     */
    public function update($user, $event)
    {
        return $this->isOwner($user, $event);
    }

    /**
     * Check if user can delete event
     * @param $user
     * @param $event
     * @return bool
     */
    public function delete($user, $event)
    {
        return $this->isOwner($user, $event);
    }
}
