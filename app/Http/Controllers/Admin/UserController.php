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

namespace App\Http\Controllers\Admin;

use Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordFormRequest;
use App\Repositories\Interfaces\User;
use App\Http\Requests\EditUserFormRequest;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ResetsPasswords;
    
    /**
     * @var User
     */
    public $userContract;

    /**
     * UserController constructor.
     * @param User $userContract
     */
    public function __construct(User $userContract)
    {
        $this->userContract = $userContract;
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route('admin.users.edit', [request()->user()->id]);
    }

    /**
     * Redirect to edit page.
     *
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($userId)
    {
        return redirect()->route('admin.users.edit', [$userId]);
    }

    /**
     * Show the form for user edit.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function edit()
    {
        $user = $this->userContract->findWith(request()->user()->id);

        if ($user->cannot('update', $user))
        {
            Flash::warning( t('You do not have sufficient permissions.'));

            return redirect()->route('admin.get.index');
        }

        $cancel = route('admin.get.index');

        return view('user.edit', compact('user', 'cancel'));
    }

    /**
     * Update the specified resource in storage
     * @param EditUserFormRequest $request
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EditUserFormRequest $request, $userId)
    {
        $user = $this->userContract->findWith($userId);

        if ($user->cannot('update', $user))
        {
            Flash::warning( t('You do not have sufficient permissions.'));

            return redirect()->route('admin.get.index');
        }

        $result = $this->userContract->update($request->all(), $user->id);


        if ($result)
        {
            Flash::success(t('Record was updated successfully.'));
        }
        else
        {
            Flash::error(trans('Error while updating record.'));
        }

        return redirect()->route('admin.users.edit', [$user->id]);
    }

    /**
     * Process a password change request.
     *
     * @param PasswordFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pass(PasswordFormRequest $request)
    {
        $user = $this->userContract->find($request->route('id'));

        if ( ! policy($user)->pass($user))
        {
            Flash::warning( t('You do not have sufficient permissions.'));

            return redirect()->route('admin.get.index');
        }

        if ( ! Hash::check($request->input('oldPassword'), $user->password))
        {
            Flash::error(t('You did not provide the correct original password.'));

            return redirect()->route('admin.users.edit', [$user->id]);
        }

        $this->resetPassword($user, $request->input('newPassword'));

        Flash::success(t('Your password has been changed.'));

        return redirect()->route('admin.users.edit', [$user->id]);
    }
}