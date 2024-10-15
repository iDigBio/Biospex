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

namespace App\Http\Controllers\Admin;

use App\Facades\DateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EditUserFormRequest;
use App\Models\User;
use App\Services\Permission\CheckPermission;
use Redirect;
use View;

/**
 * Class UserController
 */
class UserController extends Controller
{
    /**
     * Show the form for user edit.
     *
     * @throws \Exception
     */
    public function edit(User $user): mixed
    {
        if (! CheckPermission::handle('edit', $user)) {
            return Redirect::route('admin.projects.index');
        }

        $timezones = DateHelper::timeZoneSelect();

        return View::make('admin.user.edit', compact('user', 'timezones'));
    }

    /**
     * Update the specified resource in storage
     */
    public function update(User $user, EditUserFormRequest $request): mixed
    {
        if (! CheckPermission::handle('update', $user)) {
            return Redirect::route('admin.projects.index');
        }

        $request['notification'] = isset($request['notification']) ? 1 : 0;

        $result = $user->fill($request->all())->save();
        $user->profile->fill($request->all())->save();

        return $result === true ?
            Redirect::route('admin.users.edit', [$user])->with('success', t('User profile updated.')) :
            Redirect::route('admin.users.edit', [$user])->with('error', t('User profile could not be updated.'));
    }
}
