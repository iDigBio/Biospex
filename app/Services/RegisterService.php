<?php

namespace App\Services;

use App\Facades\Flash;
use App\Notifications\UserActivation;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Invite;
use App\Repositories\Interfaces\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class RegisterService
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
     * RegisterService constructor.
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
     * Register user.
     *
     * @param $request
     * @return bool
     */
    public function registerUser($request)
    {
        try
        {
            $input = $request->only('email', 'password', 'first_name', 'last_name', 'invite');
            $input['password'] = Hash::make($input['password']);
            $user = $this->userContract->create($input);

            if ( ! empty($input['invite']))
            {
                $result = $this->inviteContract->findBy('code', $input['invite']);
                if ($result->email === $user->email)
                {
                    $group = $this->groupContract->find($result->group_id);
                    $user->assignGroup($group);
                    $this->inviteContract->delete($result->id);
                }
            }

            $user->notify(new UserActivation(route('app.get.activate', [$user->id, $user->activation_code])));
            Flash::success(trans('messages.new_account'));

            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * Get invite for registration form.
     *
     * @return array
     */
    public function registrationFormInvite()
    {
        $code = Route::input('code');

        $invite = $this->inviteContract->findBy('code', $code);

        if ( ! empty($code) && ! $invite)
        {
            Flash::warning( trans('messages.invite_not_found'));
        }

        $code = isset($invite->code) ? $invite->code : null;
        $email = isset($invite->email) ? $invite->email : null;

        return ['code' => $code, 'email' => $email];
    }

    /**
     * Activate the user.
     *
     * @return string
     */
    public function activateUser()
    {
        $userId = Route::input('id');
        $code = Route::input('code');
        $user = $this->userContract->find($userId);

        if ( ! $user)
        {
            Flash::error(trans('messages.not_found'));
            return 'home';
        }

        if ($user->activated)
        {
            Flash::info(trans('messages.already_activated'));
            return 'home';
        }

        $user->attemptActivation($code);
        Flash::success(trans('messages.activated'));
        return 'app.get.login';
    }

    /**
     * Resend the UserRegistered email with activation link.
     *
     * @param $request
     * @return string
     */
    public function resendActivation($request)
    {
        $user = $this->userContract->findBy('email', $request->only('email'));

        if ( ! $user)
        {
            Flash::error(trans('messages.not_found'));
            return 'app.get.resend';
        }

        if ($user->activated)
        {
            Flash::success(trans('messages.already_activated'));
            return 'app.get.login';
        }

        $user->getActivationCode();
        $user->notify(new UserActivation(route('app.get.activate', [$user->id, $user->activation_code])));
        Flash::success(trans('messages.email_confirm'));

        return 'home';
    }
}