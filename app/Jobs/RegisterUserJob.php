<?php

namespace App\Jobs;

use App\Exceptions\BiospexException;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Contracts\UserContract;
use App\Repositories\Contracts\InviteContract;
use App\Repositories\Contracts\GroupContract;

class RegisterUserJob extends Job
{
    use SerializesModels;

    /**
     * @var Request
     */
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
     * @param UserContract $userContract
     * @param InviteContract $inviteContract
     * @param GroupContract $groupContract
     * @return bool
     */
    public function handle(
        UserContract $userContract,
        InviteContract $inviteContract,
        GroupContract $groupContract
    )
    {
        try {
            $input = $this->request->only('email', 'password', 'first_name', 'last_name', 'invite');
            $input['password'] = bcrypt($input['password']);
            $user = $userContract->create($input);

            if ( ! empty($input['invite'])) {
                $result = $inviteContract->setCacheLifetime(0)
                    ->where('code', '=', $input['invite'])
                    ->findFirst();
                if ($result->email === $user->email) {
                    $group = $groupContract->setCacheLifetime(0)->find($result->group_id);
                    $user->assignGroup($group);
                    $inviteContract->delete($result->id);
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
