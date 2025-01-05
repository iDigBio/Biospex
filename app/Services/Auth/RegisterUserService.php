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

use App\Facades\DateHelper;
use App\Models\GroupInvite;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use View;

class RegisterUserService
{
    /**
     * Show the registration form.
     *
     * @throws \Exception
     */
    public function showForm(?GroupInvite $invite = null): \Illuminate\Contracts\View\View|RedirectResponse
    {
        if (! config('config.app_registration')) {
            return \Redirect::route('home')->with('error', t('Registration is not available at this time.'));
        }

        $timezones = DateHelper::timeZoneSelect();

        return View::make('auth.register', compact('invite', 'timezones'));
    }

    /**
     * Register a new user.
     */
    public function registerUser(array $request, ?GroupInvite $invite = null): User
    {
        $request['password'] = Hash::make($request['password']);
        $user = User::create($request);
        $user->profile()->create($request);

        if (! is_null($invite)) {
            if ($invite->email === $user->email) {
                $invite->load('group');
                $user->assignGroup($invite->group);
                $invite->delete();
            }
        }

        return $user;
    }
}
