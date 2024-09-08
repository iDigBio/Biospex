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

use App\Http\Controllers\Controller;
use App\Http\Requests\EditUserFormRequest;
use App\Http\Requests\PasswordFormRequest;
use App\Services\User\UserAccountService;
use Illuminate\Foundation\Auth\ResetsPasswords;

/**
 * Class UserAccountController
 */
class UserAccountController extends Controller
{
    use ResetsPasswords;

    private UserAccountService $userAccountService;

    public function __construct(UserAccountService $userAccountService)
    {
        $this->userAccountService = $userAccountService;
    }

    /**
     * Show the form for user edit.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     *
     * @throws \Exception
     */
    public function edit(int $userId)
    {
        return $this->userAccountService->editUserProfile($userId);
    }

    /**
     * Update the specified resource in storage
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EditUserFormRequest $request)
    {
        return $this->userAccountService->updateUserProfile($request);
    }

    /**
     * Update the user password.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function password(PasswordFormRequest $request)
    {
        return $this->userAccountService->updateUserPassword($request);
    }
}
