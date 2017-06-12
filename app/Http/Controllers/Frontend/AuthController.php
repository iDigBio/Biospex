<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Requests\ResendActivationFormRequest;
use App\Repositories\Contracts\GroupContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\Jobs\RegisterUserJob;
use App\Repositories\Contracts\InviteContract;
use App\Repositories\Contracts\UserContract;
use Illuminate\Routing\Router;
use App\Events\UserRegisteredEvent;

class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * @var string
     */
    protected $redirectPath = '/projects';

    /**
     * @var string
     */
    public $loginView = 'frontend.auth.login';

    /**
     * @var UserContract
     */
    public $userContract;

    /**
     * @var Router
     */
    public $route;

    /**
     * @var GroupContract
     */
    public $groupContract;

    /**
     * AuthController constructor.
     * @param UserContract $userContract
     * @param Router $route
     * @param GroupContract $groupContract
     */
    public function __construct(
        UserContract $userContract,
        Router $route,
        GroupContract $groupContract
    )
    {
        $this->userContract = $userContract;
        $this->route = $route;
        $this->groupContract = $groupContract;
    }

    /**
     * Authenticate user
     *
     * @param Request $request
     * @param $user
     * @return \Illuminate\Http\RedirectResponse1
     */
    public function authenticated(Request $request, $user)
    {
        if ( ! $user->activated) {
            session_flash_push('error', trans('users.notactive', ['url' => route('auth.get.resend')]));
            Auth::logout();
            return redirect()->route('home');
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Show the application registration form.
     *
     * @param InviteContract $inviteContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getRegister(InviteContract $inviteContract)
    {
        $registration = config('config.registration');
        if (!$registration) {
            return redirect()->route('home')->with('error', trans('users.inactive_reg'));
        }

        $code = $this->route->input('code');

        $invite = $inviteContract->where('code', '=', $code)->findFirst();

        if (!empty($code) && !$invite) {
            session_flash_push('warning', trans('groups.invite_not_found'));
        }

        $code = isset($invite->code) ? $invite->code : null;
        $email = isset($invite->email) ? $invite->email : null;

        return view('frontend.auth.register', compact('code', 'email'));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param RegisterFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postRegister(RegisterFormRequest $request)
    {
        $user = $this->dispatch(new RegisterUserJob($request));
        
        if ($user)
        {
            event(new UserRegisteredEvent($user));
            session_flash_push('success', trans('users.created'));

            return redirect()->route('home');
        }

        return redirect()->back()->withInput();
    }

    /**
     * Get activation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getActivate()
    {
        $id = $this->route->input('id');
        $code = $this->route->input('code');
        $user = $this->userContract->find($id);

        if ( ! $user) {
            session_flash_push('error', trans('users.notfound'));

            return redirect()->route('home');
        } elseif ($user->activated) {
            session_flash_push('warning', trans('users.already_activated'));

            return redirect()->route('home');
        } elseif ($user->attemptActivation($code)) {
            session_flash_push('success', trans('users.activated'));

            return redirect()->route('auth.get.login');
        }
    }

    /**
     * Show resend activation form
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getResendActivation()
    {
        return view('frontend.auth.resend');
    }

    /**
     * Send activation link
     *
     * @param ResendActivationFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postResendActivation(ResendActivationFormRequest $request)
    {
        $user = $this->userContract->where('email', '=', $request->only('email'))->findFirst();

        if ( ! $user)
        {
            session_flash_push('error', trans('users.notfound'));

            return redirect()->route('auth.get.resend');
        }

        if ($user->activated)
        {
            session_flash_push('success', trans('users.already_activated'));

            return redirect()->route('auth.get.login');
        }

        event(new UserRegisteredEvent($user));

        session_flash_push('success', trans('users.emailconfirm'));

        return redirect()->route('home');

    }
}
