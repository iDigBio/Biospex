<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterFormRequest;
use App\Http\Requests\ResendActivationFormRequest;
use App\Repositories\Contracts\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\Jobs\RegisterUser;
use Illuminate\Config\Repository as Config;
use App\Repositories\Contracts\Invite;
use App\Repositories\Contracts\User;
use Illuminate\Routing\Router;
use Illuminate\Events\Dispatcher as Event;
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
     * @var Group
     */
    private $group;

    /**
     * AuthController constructor.
     * @param Config $config
     * @param User $user
     * @param Router $route
     * @param Group $group
     */
    public function __construct(
        Config $config,
        User $user,
        Router $route,
        Group $group
    )
    {
        $this->config = $config;
        $this->user = $user;
        $this->route = $route;
        $this->group = $group;
    }

    /**
     * Authenticate user
     *
     * @param $request
     * @param $user
     * @return \Illuminate\Http\RedirectResponse
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
     * @param Invite $repository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getRegister(Invite $repository)
    {
        $registration = $this->config->get('config.registration');
        if (!$registration) {
            return redirect()->route('home')->with('error', trans('users.inactive_reg'));
        }

        $code = $this->route->input('code');

        $invite = $repository->where(['code' => $code])->first();

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
     * @param Event $dispatcher
     * @return \Illuminate\Http\RedirectResponse
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
        return view('frontend.auth.resend');
    }

    /**
     * Send activation link
     * @param ResendActivationFormRequest $request
     * @param Event $dispatcher
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postResendActivation(ResendActivationFormRequest $request, Event $dispatcher)
    {
        $user = $this->user->where(['email' => $request->only('email')])->first();

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
