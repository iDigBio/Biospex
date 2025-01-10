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
use App\Models\GroupInvite;
use App\Models\User;
use Illuminate\Support\Collection;

class GroupInviteService
{
    public function __construct(
        protected GroupInvite $groupInvite,
        protected User $user
    ) {}

    /**
     * Create and send invites to group.
     */
    public function storeInvites(Group &$group, array $request = []): Collection
    {
        $requestInvites = collect($request['invites'])->reject(function ($invite) {
            return empty($invite['email']);
        })->pluck('email')->diff($group->invites->pluck('email'));

        return $requestInvites->reject(function ($invite) use ($group) {
            return $this->checkExistingUser($invite, $group);
        })->map(function ($invite) use ($group) {
            return $this->createNewInvite($group, $invite);
        });
    }

    /**
     * Check for existing users, if in group or need to be assigned.
     */
    private function checkExistingUser(string $email, Group $group): bool
    {
        $user = $this->user->where('email', $email)->first();

        if ($user === null) {
            return false;
        }

        if ($user->hasGroup($group)) {
            return true;
        }

        $user->assignGroup($group);

        return true;
    }

    /**
     * Create new invite.
     */
    private function createNewInvite(Group $group, string $email): \App\Models\GroupInvite
    {
        $inviteData = [
            'group_id' => $group->id,
            'email' => trim($email),
        ];

        return $this->groupInvite->create($inviteData);
    }
}
