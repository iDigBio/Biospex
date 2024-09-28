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

namespace App\Services\Group;

use App\Models\Group;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

readonly class GroupService
{
    public function __construct(
        public Group $group,
        public User $user
    ) {}

    /**
     * Display groups.
     */
    public function getAdminIndex(): Collection
    {
        return $this->group->withCount(['projects', 'expeditions', 'users'])
            ->whereHas('users', function ($q) {
                $q->where('user_id', Auth::id());
            })->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeGroup(User $user, array $request): Group
    {
        $group = $this->group->create(['user_id' => $user->id, 'title' => $request['title']]);

        $user->assignGroup($group);
        $admin = $this->user::find(1);
        $admin->assignGroup($group);

        event('group.saved');

        return $group;
    }

    /**
     * Show group.
     */
    public function showGroup(Group &$group): void
    {
        $group->load([
            'projects',
            'expeditions',
            'geoLocateForms.expeditions:id,project_id,geo_locate_form_id',
            'owner.profile',
            'users.profile',
        ])->loadCount('expeditions');
    }

    /**
     * Show group edit form.
     */
    public function editGroup(Group &$group): Collection
    {
        $group->load(['owner', 'users.profile']);

        return $group->users->mapWithKeys(function ($user) {
            return [$user->id => $user->profile->full_name];
        });
    }

    /**
     * Delete the specified resource from storage.
     *
     * @throws \Exception
     */
    public function deleteGroup(Group &$group): void
    {
        $group->loadCount(['panoptesProjects'])->load([
            'projects' => function ($q) {
                $q->withCount('workflowManagers');
            },
        ]);

        if ($group->panoptes_projects_count > 0 || $group->projects->sum('id') > 0) {
            throw new Exception(t('An Expedition workflow or process exists and cannot be deleted. Even if the process has been stopped locally, other services may need to refer to the existing Expedition.'));
        }
    }

    /**
     * Check group count for admin welcome/index page.
     */
    public function getUserGroupCount($userId): int
    {
        return $this->group->withCount([
            'users' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            },
        ])->whereHas('users', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->pluck('users_count')->sum();
    }

    /**
     * Get group select for user.
     */
    public function getUsersGroupsSelect(User $user): array
    {
        return $this->group->whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('title', 'id')->toArray();
    }

    /**
     * Get group ids for user session.
     */
    public function getUserGroupIds($userId): \Illuminate\Support\Collection
    {
        return $this->group->whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get()->map(function ($item) {
            return $item['id'];
        });
    }
}
