<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\Contracts\User;
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
     * @var User
     */
    private $user;

    /**
     * PasswordController constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getEmail()
    {
        return view('frontend.auth.password');
    }

    /**
     * Display the password reset view for the given token.
     *
     * @param null $token
     * @return $this
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
        $user = $this->user->find($request->route('id'));

        if ( ! policy($user)->pass($user))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('projects.get.index');
        }

        if ( ! Hash::check($request->input('oldPassword'), $user->password))
        {
            session_flash_push('error', trans('users.oldpassword'));

            return redirect()->route('users.get.edit', [$user->id]);
        }

        $this->resetPassword($user, $request->input('newPassword'));

        session_flash_push('success', trans('users.passwordchg'));

        return redirect()->route('users.get.edit', [$user->id]);
    }
}
