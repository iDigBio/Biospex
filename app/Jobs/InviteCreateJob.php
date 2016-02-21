<?php

namespace App\Jobs;

use App\Events\SendInviteEvent;
use App\Repositories\Contracts\Invite;
use App\Repositories\Contracts\User;
use Illuminate\Events\Dispatcher as Event;

class InviteCreateJob extends Job
{
    public $request;
    public $group;
    /**
     * @var User
     */
    private $user;

    /**
     * InviteCreateJob constructor.
     * @param $request
     * @param $group
     */
    public function __construct($request, $group)
    {
        $this->request = $request;
        $this->group = $group;
    }

    /**
     * Handle the job.
     *
     * @param User $user
     * @param Invite $invite
     * @param Event $dispatcher
     * @return mixed
     */
    public function handle(User $user, Invite $invite, Event $dispatcher)
    {
        $emails = explode(',', $this->request->get('emails'));
        $invites = $this->group->invites->toArray();

        foreach ($emails as $email) {
            $email = trim($email);
            if (is_int(array_search($email, array_column($invites, 'email'))))
            {
                session_flash_push('info', trans('groups.invite_duplicate', ['group' => $this->group->name, 'email' => $email]));

                continue;
            }

            $emailUser = $user->findByEmail($email);
            if ($emailUser)
            {
                $emailUser->assignGroup($this->group);
                session_flash_push('success', trans('groups.user_added', ['email' => $email]));

                continue;
            }

            // add invite
            $code = str_random(10);
            $inviteData = [
                'group_id' => $this->group->id,
                'email'    => trim($email),
                'code'     => $code
            ];

            $invite->create($inviteData);

            $data = [
                'email'   => $email,
                'group'  => $this->group->name,
                'code' => $code
            ];

            $dispatcher->fire(new SendInviteEvent($data));

            session_flash_push('success', trans('groups.send_invite_success', ['group' => $this->group->name, 'email' => $email]));
        }

        return;
    }
}
