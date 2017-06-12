<?php

namespace App\Jobs;

use App\Events\SendInviteEvent;
use App\Repositories\Contracts\GroupContract;
use App\Repositories\Contracts\InviteContract;
use App\Repositories\Contracts\UserContract;

class InviteCreateJob extends Job
{

    /**
     * @var
     */
    private $request;

    /**
     * @var
     */
    private $groupId;

    /**
     * InviteCreateJob constructor.
     * @param $request
     * @param $groupId
     */
    public function __construct($request, $groupId)
    {
        $this->request = $request;
        $this->groupId = $groupId;
    }

    /**
     * Handle job.
     *
     * @param UserContract $userContract
     * @param InviteContract $inviteContract
     * @param GroupContract $groupContract
     */
    public function handle(
        UserContract $userContract,
        InviteContract $inviteContract,
        GroupContract $groupContract
    )
    {
        $invites = $this->request->get('invites');
        $group = $groupContract->setCacheLifetime(0)->find($this->groupId);
        $existing = $inviteContract->setCacheLifetime(0)
            ->with('group')
            ->where('group_id', '=', $group->id)
            ->findAll();

        foreach ($invites as $invite) {
            $email = trim($invite['email']);

            $filtered = $existing->where('email', $email)->first();
            if ($filtered !== null)
            {
                $this->createEvent($email, $group->title, $filtered->code);
                session_flash_push('success', trans('groups.send_invite_success', ['group' => $group->title, 'email' => $email]));

                continue;
            }

            $user = $userContract->setCacheLifetime(0)
                ->where('email', '=', $email)
                ->findFirst();
            if ($user)
            {
                if ($user->hasGroup($group))
                {
                    session_flash_push('success', trans('groups.user_already_added', ['email' => $email]));
                }
                else
                {
                    $user->assignGroup($group);
                    session_flash_push('success', trans('groups.user_added', ['email' => $email]));
                }

                continue;
            }

            // add invite
            $code = str_random(10);
            $inviteData = [
                'group_id' => $this->groupId,
                'email'    => trim($email),
                'code'     => $code
            ];

            $inviteContract->create($inviteData);

            $this->createEvent($email, $group->title, $code);

            session_flash_push('success', trans('groups.send_invite_success', ['group' => $group->title, 'email' => $email]));
        }

    }

    /**
     * Create event data and fire.
     *
     * @param $email
     * @param $groupName
     * @param $code
     */
    private function createEvent($email, $groupName, $code)
    {
        $data = [
            'email'   => $email,
            'group'  => $groupName,
            'code' => $code
        ];

        event(new SendInviteEvent($data));
    }
}
