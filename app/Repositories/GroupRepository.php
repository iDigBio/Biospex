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

namespace App\Repositories;

use App\Models\Group;
use PhpParser\Builder;

/**
 * Class GroupRepository
 *
 * @package App\Repositories
 */
class GroupRepository extends BaseRepository
{
    /**
     * GroupRepository constructor.
     *
     * @param \App\Models\Group $group
     */
    public function __construct(Group $group)
    {

        $this->model = $group;
    }


    /**
     * Get group ids for user session.
     *
     * @param $userId
     * @return \App\Models\Group[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function getUserGroupIds($userId)
    {
        return $this->model->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get()->map(function ($item) {
            return $item['id'];
        });
    }

    /**
     * Get group select for user.
     *
     * @param $user
     * @return array
     */
    public function getUsersGroupsSelect($user): array
    {
        return $this->model->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('title', 'id')->toArray();
    }

    /**
     * Check group count for admin welcome/index page.
     *
     * @param $userId
     * @return int
     */
    public function getUserGroupCount($userId): int
    {
        return $this->model->withCount(['users' => function($q) use($userId) {
            $q->where('user_id', $userId);
        }])->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('users_count')->sum();
    }
}