<?php

namespace App\Http\Controllers\ApiAuth;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Notifications\UserActivation;
use App\Repositories\Interfaces\ApiUser;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Requests\ResendActivationFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
     * @var ApiUser
     */
    private $apiUser;

    /**
     * Create a new controller instance.
     * @param ApiUser $apiUser
     */
    public function __construct(ApiUser $apiUser)
    {
        $this->middleware('guest:apiuser');
        $this->apiUser = $apiUser;
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
        $user = $this->apiUser->create($input);

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
