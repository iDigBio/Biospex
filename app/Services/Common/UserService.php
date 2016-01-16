<?php

namespace App\Services\Common;

use App\Events\UserRegisteredEvent;
use Cartalyst\Sentry\Sentry;
use App\Repositories\Contracts\Invite;
use Illuminate\Events\Dispatcher as Event;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator as Url;
use Illuminate\Config\Repository;
use App\Repositories\Contracts\User;

class UserService
{
    /**
     * @var Sentry
     */
    private $sentry;
    /**
     * @var Invite
     */
    private $invite;
    /**
     * @var Router
     */
    private $router;
    /**
     * @var Repository
     */
    private $config;
    /**
     * @var User
     */
    private $user;
    /**
     * @var Event
     */
    private $event;
    /**
     * @var Url
     */
    private $url;

    /**
     * @param Sentry $sentry
     * @param Invite $invite
     * @param Router $router
     * @param Repository $config
     * @param User $user
     * @param Event $event
     * @param Url $url
     */
    public function __construct(
        Sentry $sentry,
        Invite $invite,
        Router $router,
        Repository $config,
        User $user,
        Event $event,
        Url $url
    ) {
        $this->sentry = $sentry;
        $this->invite = $invite;
        $this->router = $router;
        $this->config = $config;
        $this->user = $user;
        $this->event = $event;
        $this->url = $url;
    }

    /**
     * Show all users in admin pages.
     *
     * @return array
     */
    public function index()
    {
        $users = $this->sentry->findAllUsers();

        return compact('users');
    }


    /**
     * Edit user form.
     *
     * @return array|bool
     */
    public function edit($user)
    {
        if (is_null($user) || ! is_numeric($user->id)) {
            session_flash_push('error', trans('pages.error_missing_variable'));

            return false;
        }

        $timezones = timezone_select();
        $cancel = $this->url->route('projects.get.index');

        return compact('user', 'timezones', 'cancel');
    }

    public function update($request)
    {
        $result = $this->user->update($request->all());

        if ($result)
        {
            session_flash_push('success', trans('users.updated'));

            return;
        }

        session_flash_push('error', trans('users.notupdated'));

        return;
    }

}