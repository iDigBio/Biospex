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

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterFormRequest;
use App\Repositories\GroupRepository;
use App\Repositories\InviteRepository;
use App\Repositories\UserRepository;
use DateHelper;
use Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;

/**
 * Class RegisterController
 */
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/email/verify';

    /**
     * @var string
     */
    public $loginView = 'auth.login';

    /**
     * @var \App\Repositories\InviteRepository
     */
    private $inviteRepo;

    /**
     * Create a new controller instance.
     */
    public function __construct(InviteRepository $inviteRepo)
    {
        $this->middleware('guest');
        $this->inviteRepo = $inviteRepo;
    }

    /**
     * Show registration form. Overrides trait so Invite code can be checked.
     *
     * @throws \Exception
     */
    public function showRegistrationForm(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        if (! config('config.app_registration')) {
            return \Redirect::route('home')->with('error', t('Registration is not available at this time.'));
        }

        $code = request('code');

        $invite = $this->inviteRepo->findBy('code', $code);

        if (! empty($code) && ! $invite) {
            \Flash::warning(t('Your invite was unable to be found. Please contact the administration.'));
        }

        $code = $invite->code ?? null;
        $email = $invite->email ?? null;
        $timezones = ['' => null] + DateHelper::timeZoneSelect();

        return \View::make('auth.register', compact('code', 'email', 'timezones'));
    }

    /**
     * Register the user. Overrides trait so invite is checked.
     */
    public function register(RegisterFormRequest $request, UserRepository $userRepo, GroupRepository $groupRepo): \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
    {
        $input = $request->only('email', 'password', 'first_name', 'last_name', 'invite');
        $input['password'] = Hash::make($input['password']);
        $user = $userRepo->create($input);

        if (! empty($input['invite'])) {
            $invite = $this->inviteRepo->findBy('code', $input['invite']);
            if ($invite->email === $user->email) {
                $group = $groupRepo->find($invite->group_id);
                $user->assignGroup($group);
                $invite->delete();
            }
        }

        event(new Registered($user));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
}
