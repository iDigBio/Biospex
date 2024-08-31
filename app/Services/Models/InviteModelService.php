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

namespace App\Services\Models;

use App\Http\Requests\InviteFormRequest;
use App\Models\Invite;
use App\Models\Group;
use App\Notifications\GroupInvite;
use App\Services\Models\UserModelService;
use Exception;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Class InviteModelService
 *
 * @package App\Services\Process
 */
readonly class InviteModelService
{
    /**
     * InviteModelService constructor.
     *
     * @param \App\Services\Models\UserModelService $userModelService
     * @param \App\Models\Invite $invite
     * @param \App\Services\Models\GroupModelService $groupModelService
     */
    public function __construct(
        private UserModelService $userModelService,
        private Invite $invite,
        private GroupModelService $groupModelService
    )
    {}

    /**
     * Create and send invites to group.
     *
     * @param int $groupId
     * @param \App\Http\Requests\InviteFormRequest $request
     * @return bool
     */
    public function storeInvites(int $groupId, InviteFormRequest $request): bool
    {
        $group = $this->groupModelService->findWithRelations($groupId, ['invites']);

        try {
            $requestInvites = collect($request->get('invites'))->reject(function($invite){
                return empty($invite['email']);
            })->pluck('email')->diff($group->invites->pluck('email'));

            $newInvites = $requestInvites->reject(function ($invite) use($group) {
                return $this->checkExistingUser($invite, $group);
            })->map(function ($invite) use ($group) {
                return $this->createNewInvite($invite, $group);
            });

            Notification::send($newInvites, new GroupInvite($group));
            Notification::send($group->invites, new GroupInvite($group));

            \Flash::success(t('Invites to %s sent successfully.', $group->title));

            return true;
        }
        catch (Exception $e)
        {
            \Flash::error(t('Unable to sent invites for %s. Please contact the administration.', $group->title));

            return false;
        }
    }

    /**
     * Check for existing users, if in group or need to be assigned.
     *
     * @param string $email
     * @param \App\Models\Group $group
     * @return bool
     */
    private function checkExistingUser(string $email, Group $group): bool
    {
        $user = $this->userModelService->getFirstBy('email',$email);

        if ($user === null)
        {
            return false;
        }

        if ($user->hasGroup($group))
        {
            return true;
        }

        $user->assignGroup($group);

        return true;
    }

    /**
     * Create new invite.
     *
     * @param string $email
     * @param \App\Models\Group $group
     * @return \App\Models\Invite
     */
    private function createNewInvite(string $email, Group $group): \App\Models\Invite
    {
        $inviteData = [
            'group_id' => $group->id,
            'email'    => trim($email),
            'code'     => Str::random(10)
        ];

        return $this->invite->create($inviteData);
    }
}