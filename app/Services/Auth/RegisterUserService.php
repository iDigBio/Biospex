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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Auth;

use App\Models\Group;
use App\Models\Invite;
use App\Models\User;
use Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use View;

class RegisterUserService
{
    /**
     * Show the registration form.
     *
     * @throws \Exception
     */
    public function showForm(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        if (! config('config.app_registration')) {
            return \Redirect::route('home')->with('error', t('Registration is not available at this time.'));
        }

        $code = request('code');

        $invite = Invite::where('code', $code)->first();

        if (! empty($code) && ! $invite) {
            Session::flash('warning', t('Your invite was unable to be found. Please contact the administration.'));
        }

        $code = $invite->code ?? null;
        $email = $invite->email ?? null;
        $timezones = Date::timeZoneSelect();

        return View::make('auth.register', compact('code', 'email', 'timezones'));
    }

    /**
     * Register a new user.
     */
    public function registerUser(): User
    {
        $input = request()->only('email', 'password', 'first_name', 'last_name', 'timezone', 'invite');
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $user->profile()->create($input);

        if (! empty($input['invite'])) {
            $invite = Invite::where('code', $input['invite'])->first();
            if ($invite->email === $user->email) {
                $group = Group::find($invite->group_id);
                $user->assignGroup($group);
                $invite->delete();
            }
        }

        return $user;
    }
}
