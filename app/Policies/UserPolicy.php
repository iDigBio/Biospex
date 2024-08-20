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

use App\Models\User;

/**
 * Class UserPolicy
 *
 * @package App\Policies
 */
class UserPolicy
{
    /**
     * @param \App\Models\User $loggedInUser
     * @return bool|null
     */
    public function before(User $loggedInUser): ?bool
    {
        return $loggedInUser->isAdmin() ? true : null;
    }

    /**
     * @param \App\Models\User $loggedInUser
     * @return bool|null
     */
    public function isAdmin(User $loggedInUser): ?bool
    {
        return $loggedInUser->isAdmin() ? true : null;
    }

    /**
     * @param \App\Models\User $loggedInUser
     * @param \App\Models\User $user
     * @return bool
     */
    public function edit(User $loggedInUser, User $user): bool
    {
        return $loggedInUser->id === $user->id;
    }

    /**
     * @param \App\Models\User $loggedInUser
     * @param \App\Models\User $user
     * @return bool
     */
    public function update(User $loggedInUser, User $user): bool
    {
        return $loggedInUser->id === $user->id;
    }

    /**
     * @param \App\Models\User $loggedInUser
     * @param \App\Models\User $user
     * @return bool
     */
    public function password(User $loggedInUser, User $user): bool
    {
        return $loggedInUser->id === $user->id;
    }

    /**
     * @param \App\Models\User $loggedInUser
     * @return bool|null
     */
    public function delete(User $loggedInUser): ?bool
    {
        return $loggedInUser->isAdmin() ? false : null;
    }
}
