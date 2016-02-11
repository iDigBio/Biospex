<?php

namespace Biospex\Http\Controllers\Front;

use Biospex\Http\Controllers\Controller;
use Biospex\Http\Requests\RegisterFormRequest;
use Biospex\Http\Requests\ResendActivationFormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Biospex\Jobs\RegisterUser;
use Illuminate\Config\Repository as Config;
use Biospex\Repositories\Contracts\Invite;
use Biospex\Repositories\Contracts\User;
use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher as Event;
use Biospex\Events\UserRegisteredEvent;

class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * @var string
     */
    protected $loginPath = '/login';

    /**
     * @var string
     */
    protected $redirectPath = '/projects';

    /**
     * @var Config
     */
    public $config;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Router
     */
    public $route;

    /**
     * AuthController constructor.
     * @param Config $config
     * @param User $user
     * @param Router $route
     */
    public function __construct(
        Config $config,
        User $user,
        Router $route
    )
    {
        $this->config = $config;
        $this->user = $user;
        $this->route = $route;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function getLogin()
    {
        return view('front.auth.login');
    }

    /**
     * Authenticate user
     * @param $request
     * @param $user
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function authenticated($request, $user)
    {
        if ( ! $user->activated) {
            session_flash_push('error', trans('users.notactive', ['url' => route('auth.get.resend')]));
            Auth::logout();
            return redirect($this->loginPath());
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Show the application registration form
     * @param Invite $inviteRepo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getRegister(Invite $inviteRepo)
    {
        $registration = $this->config->get('config.registration');
        if (!$registration) {
            return redirect()->route('home')->with('error', trans('users.inactive_reg'));
        }

        $code = $this->route->input('code');

        $invite = $inviteRepo->findByCode($code);

        if (!empty($code) && !$invite) {
            session_flash_push('warning', trans('groups.invite_not_found'));
        }

        $code = isset($invite->code) ? $invite->code : null;
        $email = isset($invite->email) ? $invite->email : null;

        return view('front.auth.register', compact('code', 'email'));
    }

    /**
     * Handle a registration request for the application
     * @param RegisterFormRequest $request
     * @param Event $dispatcher
     * @return $this|\Illuminate\Http\RedirectResponse
     */
    public function postRegister(RegisterFormRequest $request, Event $dispatcher)
    {
        $user = $this->dispatch(new RegisterUser($request));
        if ($user)
        {
            $dispatcher->fire(new UserRegisteredEvent($user));
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
        $user = $this->user->find($id);

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
        return view('front.auth.resend');
    }

    /**
     * Send activation link
     * @param ResendActivationFormRequest $request
     * @param Event $dispatcher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postResendActivation(ResendActivationFormRequest $request, Event $dispatcher)
    {
        $user = $this->user->findByEmail($request->only('email'));

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

        $dispatcher->fire(new UserRegisteredEvent($user));

        session_flash_push('success', trans('users.emailconfirm'));

        return redirect()->route('home');

    }
}
