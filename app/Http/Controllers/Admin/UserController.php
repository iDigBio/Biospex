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
use App\Http\Requests\PasswordFormRequest;
use App\Repositories\UserRepository;
use Flash;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserController
 *
 * @package App\Http\Controllers\Admin
 */
class UserController extends Controller
{
    use ResetsPasswords;
    
    /**
     * @var \App\Repositories\UserRepository
     */
    public $userContract;

    /**
     * UserController constructor.
     *
     * @param \App\Repositories\UserRepository $userRepo
     */
    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
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
        $user = $this->userRepo->findWith(request()->user()->id, ['profile']);

        if ($user->cannot('update', $user))
        {
            Flash::warning( t('You do not have sufficient permissions.'));

            return redirect()->route('admin.projects.index');
        }

        $timezones = DateHelper::timeZoneSelect();
        $cancel = route('admin.projects.index');

        return view('admin.user.edit', compact('user', 'timezones', 'cancel'));
    }

    /**
     * Update the specified resource in storage
     * @param EditUserFormRequest $request
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EditUserFormRequest $request, $userId)
    {
        $user = $this->userRepo->findWith($userId, ['profile']);

        if ($user->cannot('update', $user))
        {
            Flash::warning( t('You do not have sufficient permissions.'));

            return redirect()->route('admin.projects.index');
        }

        $input = $request->all();
        $input['notification'] = $request->exists('notification') ? 1 : 0;
        $result = $this->userRepo->update($input, $user->id);

        $user->profile->fill($request->all());
        $user->profile()->save($user->profile);

        if ($result)
        {
            Flash::success(t('Record was updated successfully.'));
        }
        else
        {
            Flash::error(t('Error while updating record.'));
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
        $user = $this->userRepo->find($request->route('id'));

        if ( ! policy($user)->pass($user))
        {
            Flash::warning( t('You do not have sufficient permissions.'));

            return redirect()->route('admin.projects.index');
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
