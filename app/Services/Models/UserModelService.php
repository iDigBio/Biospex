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
     *
     * @param \App\Models\User $model
     */
    public function __construct(private User $model)
    {}

    /**
     * Find user with relations.
     *
     * @param int $id
     * @param array $relations
     * @return \App\Models\User|null
     */
    public function findWithRelations(int $id, array $relations = []): ?User
    {
        return $this->model->with($relations)->find($id);
    }

    /**
     * Get user by column.
     *
     * @param string $column
     * @param string $value
     * @return \App\Models\User|null
     */
    public function getFirstBy(string $column, string $value): ?User
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * Get users for site mailer.
     *
     * @param string $type
     * @return \Illuminate\Support\Collection
     */
    public function getUsersForMailer(string $type): Collection
    {
        if ($type === 'all') {
            return $this->getAllUsersOrderByDate();
        }

        return $this->model->has('ownGroups')->with(['profile'])->get();
    }

    /**
     * Get all users and order by date.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllUsersOrderByDate(): Collection
    {
        return $this->model->with('profile')->orderBy('created_at')->get();
    }
}