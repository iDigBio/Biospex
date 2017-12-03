<?php

namespace App\Http\Controllers\ApiAuth;

use App\Http\Controllers\Controller;
use App\Notifications\UserActivation;
use App\Repositories\Contracts\ApiUserContract;
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
     * @var ApiUserContract
     */
    private $apiUserContract;

    /**
     * Create a new controller instance.
     * @param ApiUserContract $apiUserContract
     */
    public function __construct(ApiUserContract $apiUserContract)
    {
        $this->apiUserContract = $apiUserContract;
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
        $user = $this->apiUserContract->createApiUser($input);

        if ($user)
        {
            $user->notify(new UserActivation(route('api.get.activate', [$user->id, $user->activation_code])));
            flash()->success(trans('users.created'));

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
        $user = $this->apiUserContract->findActivationCodeByUserId($userId);

        if ( ! $this->checkUserActivation($user))
        {
            return redirect()->route('api.get.index');
        }

        $user->attemptActivation($code);
        flash()->success(trans('users.activated'));

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
        $user = $this->apiUserContract->findActivationCodeByUserEmail($request->only('email'));

        if ( ! $this->checkUserActivation($user))
        {
            return redirect()->route('api.get.index');
        }

        $user->getActivationCode();
        $user->notify(new UserActivation(route('api.get.activate', [$user->id, $user->activation_code])));
        flash()->success(trans('users.emailconfirm'));

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
        $check = true;
        if ( ! $user)
        {
            flash()->error(trans('users.notfound'));
            $check = false;
        }
        elseif ($user->activated)
        {
            flash()->info(trans('users.already_activated'));
            $check = false;
        }

        return $check;
    }
}
