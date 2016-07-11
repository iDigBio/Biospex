<?php

namespace App\Jobs;

use App\Events\SendInviteEvent;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Invite;
use App\Repositories\Contracts\User;
use Illuminate\Events\Dispatcher;

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
     * @var User
     */
    private $user;

    /**
     * @var
     */
    private $dispatcher;

    /**
     * InviteCreateJob constructor.
     * @param $request
     * @param $groupId
     */
    public function __construct($request, $groupId)
    {
        $this->request = $request;
        $this->groupId = $groupId;
        $this->dispatcher = app(Dispatcher::class);
    }

    /**
     * Handle job.
     *
     * @param User $userRepo
     * @param Invite $inviteRepo
     * @param Group $groupRepo
     */
    public function handle(User $userRepo, Invite $inviteRepo, Group $groupRepo)
    {
        $invites = $this->request->get('invites');
        $group = $groupRepo->skipCache()->find($this->groupId);
        $existing = $inviteRepo->skipCache()->with(['group'])->where(['group_id' => $group->id])->get();

        foreach ($invites as $invite) {
            $email = trim($invite['email']);

            $filtered = $existing->where('email', $email)->first();
            if ($filtered !== null)
            {
                $this->createEvent($email, $group->name, $filtered->code);
                session_flash_push('success', trans('groups.send_invite_success', ['group' => $group->name, 'email' => $email]));

                continue;
            }

            $user = $userRepo->skipCache()->where(['email' => $email])->first();
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

            $inviteRepo->create($inviteData);

            $this->createEvent($email, $group->name, $code);

            session_flash_push('success', trans('groups.send_invite_success', ['group' => $group->name, 'email' => $email]));
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

        $this->dispatcher->fire(new SendInviteEvent($data));
    }
}
