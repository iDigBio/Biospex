<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Requests\ResendActivationFormRequest;
use App\Services\RegisterService;

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
    protected $redirectTo = '/projects';

    /**
     * @var string
     */
    public $loginView = 'auth.login';

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show registration form. Overrides trait so Invite code can be checked.
     *
     * @param RegisterService $registerService
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showRegistrationForm(RegisterService $registerService)
    {
        if ( ! config('config.registration')) {
            return redirect()->route('home')->with('error', trans('users.inactive_reg'));
        }

        $formVars = $registerService->registrationFormInvite();

        return view('auth.register', $formVars);
    }

    /**
     * Register the user. Overrides trait so invite is checked.
     *
     * @param RegisterFormRequest $request
     * @param RegisterService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(RegisterFormRequest $request, RegisterService $service)
    {
        $registered = $service->registerUser($request);

        return $registered ? redirect()->route('home') : redirect()->back()->withInput();
    }

    /**
     * Attempt to activate the user.
     *
     * @param RegisterService $registerService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getActivate(RegisterService $registerService)
    {
        $route = $registerService->activateUser();

        return redirect()->route($route);
    }

    /**
     * Show resend activation form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResendActivationForm()
    {
        return view('auth.resend');
    }

    /**
     * @param ResendActivationFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postResendActivation(ResendActivationFormRequest $request, RegisterService $registerService)
    {
        $route = $registerService->resendActivation($request);

        return redirect()->route($route);
    }
}
