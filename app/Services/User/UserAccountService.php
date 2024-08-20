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

namespace App\Services\User;

use Date;
use App\Http\Requests\PasswordFormRequest;
use App\Http\Requests\Request;
use App\Models\User;
use App\Services\Permission\CheckPermission;
use Flash;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class UserAccountService
{
    use ResetsPasswords;

    /**
     * Handle the user edit form.
     *
     * @param int $userId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function editUserProfile(int $userId): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = User::find($userId);

        if (!CheckPermission::handle('edit', $user)) {
            return Redirect::back();
        }

        $timezones = Date::timeZoneSelect();
        $cancel = URL::route('admin.projects.index');

        return view('admin.user.edit', compact('user', 'timezones', 'cancel'));
    }

    /**
     * Update the user profile.
     *
     * @param \App\Http\Requests\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserProfile(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = User::find($request->route('users'));

        if (!CheckPermission::handle('update', $user)) {
            return Redirect::back();
        }

        $input = $request->all();
        $input['notification'] = $request->exists('notification') ? 1 : 0;

        $result = $user->fill($input)->save();
        $user->profile->fill($request->all())->save();

        $result === true ?
            Flash::success(t('User profile updated.')) :
            Flash::error(t('User profile could not be updated.'));

        return Redirect::route('admin.users.edit', [$user->id]);
    }

    /**
     * Update the user password.
     *
     * @param \App\Http\Requests\PasswordFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateUserPassword(PasswordFormRequest $request): \Illuminate\Http\RedirectResponse
    {
        $user = User::find($request->route('users'));

        if (!CheckPermission::handle('password', $user)) {
            return Redirect::back();
        }

        $this->resetPassword($request->user(), $request->password);

        Flash::success(t('Your password has been changed.'));

        return Redirect::back();
    }
}