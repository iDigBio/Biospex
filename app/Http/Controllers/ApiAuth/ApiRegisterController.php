<?php declare(strict_types=1);
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

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use App\Services\Model\ApiUserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Requests\RegisterFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Class ApiRegisterController
 *
 * @package App\Http\Controllers\ApiAuth
 */
class ApiRegisterController extends Controller
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
    protected $redirectTo = '/api/email/verify';

    /**
     * @var \App\Services\Model\ApiUserService
     */
    private $apiUserService;

    /**
     * Create a new controller instance.
     * @param \App\Services\Model\ApiUserService $apiUserService
     */
    public function __construct(ApiUserService $apiUserService)
    {
        $this->middleware('guest:apiuser');
        $this->apiUserService = $apiUserService;
    }

    /**
     * Show api registration form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('apiauth.register');
    }

    /**
     * Register the user. Overrides trait so invite is checked.
     *
     * @param RegisterFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(RegisterFormRequest $request)
    {
        $input = $request->only('first_name', 'last_name', 'email', 'password');
        $input['password'] = Hash::make($input['password']);
        $input['name'] = $input['first_name'] . ' ' . $input['last_name'];
        $user = $this->apiUserService->create($input);

        event(new Registered($user));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /**
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard('apiuser');
    }
}
