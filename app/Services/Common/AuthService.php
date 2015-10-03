<?php

namespace App\Services\Common;

use App\Events\LostPasswordEvent;
use App\Events\ResetPasswordEvent;
use App\Events\UserLoggedInEvent;
use App\Events\UserLoggedOutEvent;
use Illuminate\Config\Repository as Config;
use App\Models\Invite;
use Cartalyst\Sentry\Users\UserAlreadyActivatedException;
use Cartalyst\Sentry\Users\UserExistsException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Illuminate\Events\Dispatcher as Event;
use Illuminate\Routing\Router;
use Cartalyst\Sentry\Sentry;

class AuthService
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var Sentry
     */
    private $sentry;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Invite
     */
    private $invite;

    /**
     * @param Config $config
     * @param Invite $invite
     * @param Event $event
     * @param Sentry $sentry
     * @param Router $router
     * @internal param Auth $auth
     */
    public function __construct(
        Config $config,
        Invite $invite,
        Event $event,
        Sentry $sentry,
        Router $router

    ) {
        $this->event = $event;
        $this->sentry = $sentry;
        $this->router = $router;
        $this->invite = $invite;
        $this->config = $config;
    }

    /**
     * Show registration form.
     *
     * @return array|bool
     */
    public function getRegister()
    {
        $registration = $this->config->get('config.registration');
        if ( ! $registration) {
            return false;
        }

        $code = $this->router->input('code');

        $invite = $this->invite->findByCode($code);

        if ( ! empty($code) && ! $invite) {
            session_flash_push('warning', trans('groups.invite_not_found'));
        }

        $code = isset($invite->code) ? $invite->code : null;
        $email = isset($invite->email) ? $invite->email : null;

        return compact('code', 'email');
    }

    /**
     * Store new user.
     *
     * @param $request
     * @return bool
     */
    public function postRegister($request)
    {
        $input = $request->only('email', 'password', 'first_name', 'last_name', 'invite');

        try {
            $user = $this->user->create($input);

            $this->addUsertoUserGroup($user);

            // Determine group creation: invite create from email
            if ( ! empty($input['invite'])) {
                $this->addGroupByInvite($input, $user);
            } else {
                $this->createGroupFromEmail($user);
            }

            session_flash_push('success', trans('users.created'));

            $event = [
                'activationCode' => $user->GetActivationCode(),
                'userId'         => $user->id,
                'email'          => $user->email
            ];

            $this->event->fire(new UserRegisteredEvent($event));

            return true;
        } catch (LoginRequiredException $e) {
            session_flash_push('warning', trans('users.loginreq'));

            return false;
        } catch (UserExistsException $e) {
            session_flash_push('warning', trans('users.exists'));

            return false;
        } catch (GroupNotFoundException $e) {
            session_flash_push('warning', trans('users.notfound'));

            return false;
        } catch (Exception $e) {
            session_flash_push('warning', $e->getMessage());

            return false;
        }
    }

    /**
     * Check user login and store.
     *
     * @param $request
     * @return bool
     */
    public function store($request)
    {
        $result = $this->auth->store($request);

        if ($result['success']) {
            $this->event->fire(new UserLoggedInEvent($result));

            return true;
        }

        session_flash_push('error', $result['message']);

        return false;
    }

    /**
     * Process forgotten password.
     *
     * @param $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forgot($request)
    {
        $user = $this->sentry->findUserByLogin($request->get('email'));

        if ($user) {
            $this->event->fire(new LostPasswordEvent($user));
            session_flash_push('success', trans('users.emailinfo'));

            return true;
        }

        session_flash_push('error', trans('users.notfound'));

        return false;
    }

    /**
     * Process reset password link for user.
     *
     * @return bool
     */
    public function reset()
    {
        $id = $this->router->input('id');
        $code = $this->router->input('code');

        $user = $this->sentry->findUserById($id);

        if ($user) {
            $newPassword = generate_password(8, 8);
            if ($user->attemptResetPassword($code, $newPassword)) {
                session_flash_push('error', trans('users.problem'));

                return false;
            }

            $this->event->fire(new ResetPasswordEvent($user->getLogin(), $newPassword));
            session_flash_push('success', trans('users.emailpassword'));

            return;
        }

        session_flash_push('error', trans('users.notfound'));

        return;
    }

    /**
     * Resend activation code for user registration.
     *
     * @param $request
     * @return bool
     */
    public function resend($request)
    {
        $user = $this->sentry->findUserByLogin($request->get('email'));

        if ($user) {
            if ($user->isActivated()) {
                session_flash_push('success', trans('users.alreadyactive'));

                return false;
            }

            $event = [
                'activationCode' => $user->GetActivationCode(),
                'userId'         => $user->id,
                'email'          => $user->email
            ];

            $this->event->fire(new UserRegisteredEvent($event));

            session_flash_push('success', trans('users.emailconfirm'));

            return true;
        }

        session_flash_push('error', trans('users.notfound'));

        return false;
    }

    /**
     * Activate new user.
     *
     * @return bool
     */
    public function activate()
    {
        $id = $this->router->input('id');
        $code = $this->router->input('code');

        try {
            $user = $this->sentry->findUserById($id);

            if ($user->attemptActivation($code)) {
                session_flash_push('success', trans('users.activated'));

                return true;
            }

            session_flash_push('error', trans('users.notactivated'));
        } catch (UserAlreadyActivatedException $e) {
            session_flash_push('success', trans('users.already_activated'));
        } catch (UserExistsException $e) {
            session_flash_push('error', trans('users.exists'));
        } catch (UserNotFoundException $e) {
            session_flash_push('error', trans('users.notfound'));
        }

        return false;
    }

    /**
     * Destroy user session.
     */
    public function destroy()
    {
        $this->auth->destroy();

        $this->event->fire(new UserLoggedOutEvent());
    }
}