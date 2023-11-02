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

use App\Notifications\GroupInvite;
use App\Repositories\GroupRepository;
use App\Repositories\InviteRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Class InviteProcess
 *
 * @package App\Services\Process
 */
class InviteProcess
{

    /**
     * @var \App\Repositories\UserRepository
     */
    private $userRepo;

    /**
     * @var \App\Repositories\InviteRepository
     */
    private $inviteRepo;

    /**
     * @var \App\Repositories\GroupRepository
     */
    private $groupRepo;

    /**
     * InviteProcess constructor.
     *
     * @param \App\Repositories\UserRepository $userRepo
     * @param \App\Repositories\InviteRepository $inviteRepo
     * @param \App\Repositories\GroupRepository $groupRepo
     */
    public function __construct(
        UserRepository $userRepo,
        InviteRepository $inviteRepo,
        GroupRepository $groupRepo
    )
    {
        $this->userRepo = $userRepo;
        $this->inviteRepo = $inviteRepo;
        $this->groupRepo = $groupRepo;
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
            $group = $this->groupRepo->find($groupId);

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

            \Flash::success(t('Invites to :group sent successfully.', $group->title));

            return true;
        }
        catch (Exception $e)
        {
            \Flash::error(t('Unable to sent invites for :group. Please contact the administration.', $group->title));

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
        $user = $this->userRepo->findBy('email',$email);

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

        return $this->inviteRepo->create($inviteData);
    }
}