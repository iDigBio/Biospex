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

namespace App\Repositories\Eloquent;

use App\Models\Group as Model;
use App\Repositories\Interfaces\Group;

class GroupRepository extends EloquentRepository implements Group
{
    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @param $user
     * @return mixed
     * @throws \Exception
     */
    public function getUsersGroupsSelect($user)
    {
        $results = $this->model->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('title', 'id')->toArray();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getUserGroupIds($userId)
    {
        $groupIds = $this->model->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get()->map(function ($item) {
                return $item['id'];
            });

        $this->resetModel();

        return $groupIds;
    }

    /**
     * @inheritdoc
     */
    public function getGroupsByUserId($userId)
    {
        $results = $this->model->withCount('projects', 'expeditions', 'users')
            ->whereHas('users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getGroupShow($groupId)
    {
        $results = $this->model->with([
            'projects',
            'owner.profile',
            'users.profile',
        ])->withCount('expeditions')->find($groupId);

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function getUserGroupCount($userId)
    {
        $results = $this->model->withCount(['users' => function($q) use($userId) {
            $q->where('user_id', $userId);
        }])->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('users_count')->sum();

        $this->resetModel();

        return $results;
    }
}