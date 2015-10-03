<?php 

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Common\AuthService;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\LostPasswordRequest;
use App\Http\Requests\ResendActivationFormRequest;

class AuthControllerOrig extends Controller
{
    /**
     * @var AuthService
     */
    private $service;

    /**
     * Constructor.
     *
     * @param AuthService $service
     */
    public function __construct(AuthService $service)
    {
        $this->service = $service;
    }

    /**
     * Show login form.
     */
    public function login()
    {
        return view('front.auth.login');
    }

    /**
     * Check user login and store.
     *
     * @param UserLoginRequest $request
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function store(UserLoginRequest $request)
    {
        if ($this->service->store($request))
            return redirect()->route('projects.index');

        return redirect()->route('auth.login');
    }

    /**
     * Show forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function password()
    {
        return view('front.auth.forgot');
    }

    /**
     * Process Forgot Password request.
     *
     * @param LostPasswordRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forgot(LostPasswordRequest $request)
    {
        if ($$this->service->forgot($request))
            return redirect()->route('auth.login');

        return redirect()->route('auth.password');
    }

    /**
     * Process a password reset request link.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset()
    {
        $$this->service->reset();

        return redirect()->route('user.login');
    }

    /**
     * Show reactivation form.
     *
     * @return \Illuminate\View\View
     */
    public function resendActivation()
    {
        return view('front.auth.resend');
    }

    /**
     * Process resend activation request.
     *
     * @param ResendActivationFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resend(ResendActivationFormRequest $request)
    {
        if ($$this->service->resend($request))
            return redirect()->route('home');

        return redirect()->route('auth.activation');
    }

    /**
     * Activate a new user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate()
    {
        if ($$this->service->activate())
            return redirect()->route('auth.login');

        return redirect()->route('home');
    }

    /**
     * Delete user session.
     *
     * @return mixed
     */
    public function destroy()
    {
        $this->service->destroy();

        return redirect()->route('home');
    }
}
