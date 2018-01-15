<?php

namespace App\Services\Model;

use App\Notifications\GroupInvite;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Invite;
use App\Repositories\Interfaces\User;
use App\Facades\Flash;
use Illuminate\Support\Facades\Notification;

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
            $existing = $this->getExistingInvites($group->id);
            $requestInvites = collect($request->get('invites'))->diff($existing->pluck('email'));

            $newInvites = $requestInvites->reject(function ($email) use($group) {
                return $this->checkExistingUser($email, $group);
            })->map(function ($email) use ($group) {
                return $this->createNewInvite($email, $group);
            });

            Notification::send($newInvites, new GroupInvite($group));

            Flash::success(trans('groups.send_invite_success', ['group' => $group->title]));

            return true;
        }
        catch (\Exception $e)
        {
            Flash::error(trans('groups.send_invite_error', ['group' => $group->title]));

            return false;
        }
    }

    /**
     * Resend an invite.
     *
     * @param $group
     * @param $inviteId
     * @return bool
     */
    public function resendInvite($group, $inviteId)
    {
        try {
            $invite = $this->inviteContract->find($inviteId);

            $invite->notify(new GroupInvite($group));

            Flash::success(trans('groups.send_invite_success', ['group' => $group->title]));

            return true;
        }
        catch (\Exception $e)
        {
            Flash::error( trans('groups.send_invite_error', ['group' => $group->title]));

            return false;
        }
    }

    /**
     * Delete invite.
     *
     * @param $inviteId
     * @return bool
     */
    public function deleteInvite($inviteId)
    {
        try{
            $this->inviteContract->delete($inviteId);

            Flash::success(trans('groups.invite_destroyed'));

            return true;
        }
        catch(\Exception $e)
        {
            Flash::error(trans('groups.invite_destroyed_failed'));

            return false;
        }
    }

    /**
     * Get any existing invites for the group.
     *
     * @param $groupId
     * @return static
     */
    private function getExistingInvites($groupId)
    {
        return $this->inviteContract->getExistingInvitesByGroupId($groupId);
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
        $code = str_random(10);
        $inviteData = [
            'group_id' => $group->id,
            'email'    => trim($email),
            'code'     => $code
        ];

        return $this->inviteContract->create($inviteData);
    }
}