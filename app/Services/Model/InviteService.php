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

namespace App\Services\Model;

use App\Notifications\GroupInvite;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Invite;
use App\Repositories\Interfaces\User;
use App\Facades\FlashHelper;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class InviteService
{

    /**
     * @var User
     */
    private $userContract;

    /**
     * @var Invite
     */
    private $inviteContract;

    /**
     * @var Group
     */
    private $groupContract;

    /**
     * InviteService constructor.
     * @param User $userContract
     * @param Invite $inviteContract
     * @param Group $groupContract
     */
    public function __construct(
        User $userContract,
        Invite $inviteContract,
        Group $groupContract
    )
    {
        $this->userContract = $userContract;
        $this->inviteContract = $inviteContract;
        $this->groupContract = $groupContract;
    }

    /**
     * Create and send invites to group.
     *
     * @param $groupId
     * @param $request
     * @return bool
     */
    public function storeInvites($groupId, $request)
    {
        try {
            $group = $this->groupContract->find($groupId);

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

            FlashHelper::success(trans('messages.send_invite_success', ['group' => $group->title]));

            return true;
        }
        catch (\Exception $e)
        {
            FlashHelper::error(trans('messages.send_invite_error', ['group' => $group->title]));

            return false;
        }
    }

    /**
     * Check for existing users, if in group or need to be assigned.
     *
     * @param $email
     * @param $group
     * @return bool
     */
    private function checkExistingUser($email, $group)
    {
        $user = $this->userContract->findBy('email',$email);

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
     * @param $email
     * @param $group
     * @return mixed
     */
    private function createNewInvite($email, $group)
    {
        $inviteData = [
            'group_id' => $group->id,
            'email'    => trim($email),
            'code'     => Str::random(10)
        ];

        return $this->inviteContract->create($inviteData);
    }
}