<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LostPasswordRequest;
use App\Jobs\SendLostPasswordJob;
use App\Jobs\ResetPasswordJob;
use App\Http\Requests\UserLoginRequest;
use App\Jobs\UserLogInJob;
use App\Jobs\UserLogOutJob;

class AuthController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Show login form
     */
    public function login()
    {
        return view('admin.auth.login');
    }

    /**
     * Check user login and store.
     *
     * @param UserLoginRequest $request
     * @return mixed
     */
    public function store(UserLoginRequest $request)
    {
        $input = $request->only('email', 'password', 'remember');
        $result = $this->dispatch(new UserLogInJob($input));

        if ($result['success']) {
            return redirect()->route('dashboard');
        }

        session_flash_push('error', $result['message']);

        return redirect()->route('admin.login')->withInput();
    }

    /**
     * Show forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function password()
    {
        return view('admin.auth.forgot');
    }

    /**
     * Process Forgot Password request
     *
     * @param LostPasswordRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forgot(LostPasswordRequest $request)
    {
        $result = $this->dispatch(new SendLostPasswordJob($request));

        if ($result) {
            return redirect()->route('admin.login');
        }

        return redirect()->route('admin.password');
    }

    /**
     * Process a password reset request link
     *
     * @param $id
     * @param $code
     * @return \Illuminate\Http\\RedirectResponse|void
     */
    public function reset($id, $code)
    {
        $this->dispatch(new ResetPasswordJob($id, $code));

        return redirect()->route('admin.login');
    }


    /**
     * Delete user session
     *
     * @return mixed
     */
    public function destroy()
    {
        $this->dispatch(new UserLogOutJob());

        return redirect()->route('admin.login');
    }
}
