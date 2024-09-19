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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Models;

use App\Models\User;
use Illuminate\Support\Collection;

readonly class UserModelService
{
    /**
     * UserModelService constructor.
     */
    public function __construct(public User $user) {}

    /**
     * Find user with relations.
     */
    public function findWithRelations(int $id, array $relations = []): ?User
    {
        return $this->user->with($relations)->find($id);
    }

    /**
     * Get user by column.
     */
    public function getFirstBy(string $column, string $value): ?User
    {
        return $this->user->where($column, $value)->first();
    }

    /**
     * Get users for site mailer.
     */
    public function getUsersForMailer(string $type): Collection
    {
        if ($type === 'all') {
            return $this->getAllUsersOrderByDate();
        }

        return $this->user->has('ownGroups')->with(['profile'])->get();
    }

    /**
     * Get all users and order by date.
     */
    public function getAllUsersOrderByDate(): Collection
    {
        return $this->user->with('profile')->orderBy('created_at')->get();
    }
}
