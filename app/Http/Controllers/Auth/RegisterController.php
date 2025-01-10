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
use App\Models\GroupInvite;
use App\Services\Auth\RegisterUserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

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
     * Create a new controller instance.
     */
    public function __construct(protected RegisterUserService $registerUserService)
    {
        $this->middleware('guest');
    }

    /**
     * Show registration form. Overrides trait so Invite code can be checked.
     *
     * @throws \Exception
     */
    public function showRegistrationForm(?GroupInvite $invite = null): View|RedirectResponse
    {
        return $this->registerUserService->showForm($invite);
    }

    /**
     * Register the user. Overrides trait so invite is checked.
     */
    public function register(RegisterFormRequest $request, ?GroupInvite $invite = null): Redirector|RedirectResponse
    {
        if (isset($invite) && $invite->email !== $request->email) {
            return redirect()->route('app.get.register', $invite)->with('danger', t('Email does not match invite email.'));
        }

        $user = $this->registerUserService->registerUser($request->all(), $invite);

        event(new Registered($user));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
}
