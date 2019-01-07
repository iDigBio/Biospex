<?php

namespace App\Http\Controllers\ApiAuth;

use App\Facades\FlashHelper;
use App\Http\Controllers\Controller;
use App\Notifications\UserActivation;
use App\Repositories\Interfaces\ApiUser;
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
    protected $redirectTo = 'api';

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
        $input = $request->only('email', 'password', 'first_name', 'last_name', 'invite');
        $input['password'] = Hash::make($input['password']);
        $input['name'] = $input['first_name'] . ' ' . $input['last_name'];
        $user = $this->apiUser->create($input);

        if ($user)
        {
            $user->notify(new UserActivation(route('api.get.activate', [$user->id, $user->activation_code])));
            FlashHelper::success(trans('messages.new_account'));

            return redirect()->route('api.get.index');
        }

        return redirect()->back()->withInput();
    }

    /**
     * Attempt to activate the user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getActivate($userId, $code)
    {
        $user = $this->apiUser->find($userId);

        if ( ! $this->checkUserActivation($user))
        {
            return redirect()->route('api.get.index');
        }

        $user->attemptActivation($code);
        FlashHelper::success(trans('messages.activated'));

        return redirect()->route('api.get.login');
    }

    /**
     * Show resend activation form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResendActivationForm()
    {
        return view('apiauth.resend');
    }

    /**
     * Resend welcome email with activation code.
     *
     * @param ResendActivationFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postResendActivation(ResendActivationFormRequest $request)
    {
        $user = $this->apiUser->findBy('email', $request->only('email'));

        if ( ! $this->checkUserActivation($user))
        {
            return redirect()->route('api.get.index');
        }

        $user->getActivationCode();
        $user->notify(new UserActivation(route('api.get.activate', [$user->id, $user->activation_code])));
        FlashHelper::success(trans('messages.email_confirm'));

        return redirect()->route('api.get.login');
    }

    /**
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard('apiuser');
    }

    /**
     * Check user exists or activated.
     *
     * @param $user
     * @return bool
     */
    protected function checkUserActivation($user)
    {
        if ( ! $user)
        {
            FlashHelper::error(trans('messages.not_found'));
            return false;
        }

        if ($user->activated)
        {
            FlashHelper::info(trans('messages.already_activated'));
            return false;
        }

        return true;
    }
}
