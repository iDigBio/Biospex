<?php
/**
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

use Flash;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Group;
use App\Repositories\Interfaces\Invite;
use App\Http\Requests\RegisterFormRequest;
use App\Repositories\Interfaces\User;
use DateHelper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Hash;

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
     * @var \App\Repositories\Interfaces\Invite
     */
    private $inviteContract;

    /**
     * Create a new controller instance.
     *
     * @param \App\Repositories\Interfaces\Invite $inviteContract
     */
    public function __construct(Invite $inviteContract)
    {
        $this->middleware('guest');
        $this->inviteContract = $inviteContract;
    }

    /**
     * Show registration form. Overrides trait so Invite code can be checked.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function showRegistrationForm()
    {
        if ( ! config('config.registration')) {
            return redirect()->route('home')->with('error', t('Registration is not available at this time.'));
        }

        $code = request('code');

        $invite = $this->inviteContract->findBy('code', $code);

        if ( ! empty($code) && ! $invite)
        {
            Flash::warning( t('Your invite was unable to be found. Please contact the administration.'));
        }

        $code = $invite->code ?? null;
        $email = $invite->email ?? null;
        $timezones = ['' => null] + DateHelper::timeZoneSelect();

        return view('auth.register', compact('code', 'email', 'timezones'));
    }

    /**
     * Register the user. Overrides trait so invite is checked.
     *
     * @param \App\Http\Requests\RegisterFormRequest $request
     * @param \App\Repositories\Interfaces\User $userContract
     * @param \App\Repositories\Interfaces\Group $groupContract
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function register(RegisterFormRequest $request, User $userContract, Group $groupContract)
    {
        $input = $request->only('email', 'password', 'first_name', 'last_name', 'invite');
        $input['password'] = Hash::make($input['password']);
        $user = $userContract->create($input);

        if ( ! empty($input['invite']))
        {
            $result = $this->inviteContract->findBy('code', $input['invite']);
            if ($result->email === $user->email)
            {
                $group = $groupContract->find($result->group_id);
                $user->assignGroup($group);
                $this->inviteContract->delete($result->id);
            }
        }

        event(new Registered($user));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
}
