<?php

namespace App\Jobs;

use App\Exceptions\BiospexException;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Invite;
use App\Repositories\Contracts\Group;

class RegisterUserJob extends Job
{
    use SerializesModels;

    public $request;

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
     * Handle job
     * @param User $userRepo
     * @param Invite $inviteRepo
     * @param Group $groupRepo
     * @return bool
     */
    public function handle(User $userRepo, Invite $inviteRepo, Group $groupRepo)
    {
        try {
            $input = $this->request->only('email', 'password', 'first_name', 'last_name', 'invite');
            $input['password'] = bcrypt($input['password']);
            $user = $userRepo->create($input);

            if ( ! empty($input['invite'])) {
                $result = $inviteRepo->skipCache()->where(['code' => $input['invite']])->first();
                if ($result->email === $user->email) {
                    $group = $groupRepo->skipCache()->find($result->group_id);
                    $user->assignGroup($group);
                    $inviteRepo->delete($result->id);
                }
            }

            return $user;
        }
        catch(BiospexException $e)
        {
            return false;
        }
    }
}
