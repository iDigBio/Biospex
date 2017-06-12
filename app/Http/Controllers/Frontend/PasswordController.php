<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\Contracts\UserContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordFormRequest;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected $redirectTo = '/projects';

    /**
     * @var UserContract
     */
    private $userContract;

    /**
     * PasswordController constructor.
     * @param UserContract $userContract
     */
    public function __construct(UserContract $userContract)
    {
        $this->userContract = $userContract;
    }

    public function getEmail()
    {
        return view('frontend.auth.password');
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param null $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws NotFoundHttpException
     */
    public function getReset($token = null)
    {
        if (is_null($token)) {
            throw new NotFoundHttpException;
        }

        return view('frontend.auth.reset')->with('token', $token);
    }

    /**
     * Process a password change request.
     * 
     * @param PasswordFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pass(PasswordFormRequest $request)
    {
        $user = $this->userContract->find($request->route('id'));

        if ( ! policy($user)->pass($user))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.projects.index');
        }

        if ( ! Hash::check($request->input('oldPassword'), $user->password))
        {
            session_flash_push('error', trans('users.oldpassword'));

            return redirect()->route('web.users.edit', [$user->id]);
        }

        $this->resetPassword($user, $request->input('newPassword'));

        session_flash_push('success', trans('users.passwordchg'));

        return redirect()->route('web.users.edit', [$user->id]);
    }
}
