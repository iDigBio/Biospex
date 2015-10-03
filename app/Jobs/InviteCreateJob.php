<?php

namespace App\Jobs;

use Illuminate\Contracts\Bus\SelfHandling;
use App\Events\SendInviteEvent;
use Cartalyst\Sentry\Users\UserNotFoundException;
use App\Repositories\Contracts\Invite;

class InviteCreateJob extends Job implements SelfHandling
{
    /**
     * Create a new job instance.
     *
     * @param $request
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Handle the job.
     *
     * @param Invite $invite
     * @return mixed
     */
    public function handle(Invite $invite)
    {
        $group = \Sentry::findGroupById($this->request->route('groups'));
        $emails = explode(',', $this->request->get('emails'));

        foreach ($emails as $email) {
            $email = trim($email);
            if ($duplicate = $invite->checkDuplicate($group->id, $email)) {
                session_flash_push('info', trans('groups.invite_duplicate', ['group' => $group->name, 'email' => $email]));
                continue;
            }

            try {
                $user = \Sentry::findUserByLogin($email);
                $user->addGroup($group);
                session_flash_push('success', trans('groups.user_added', ['email' => $email]));
            } catch (UserNotFoundException $e) {
                // add invite
                $code = str_random(10);
                $data = [
                    'group_id' => $group->id,
                    'email'    => $email,
                    'code'     => $code
                ];

                if (! $result = $invite->save($data)) {
                    session_flash_push('warning', trans('groups.send_invite_error', ['group' => $group->name, 'email' => $email]));
                } else {
                    $newInvite = [
                        'email'   => $email,
                        'subject' => trans('emails.group_invite_subject'),
                        'view'    => 'emails.group-invite',
                        'data'    => ['group' => $group->name, 'code' => $code],
                    ];

                    \Event::fire(new SendInviteEvent($newInvite));

                    session_flash_push('success', trans('groups.send_invite_success', ['group' => $group->name, 'email' => $email]));
                }
            }
        }

        return $group->id;
    }
}
