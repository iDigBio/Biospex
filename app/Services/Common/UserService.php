<?php

namespace App\Services\Common;

use App\Events\UserRegisteredEvent;
use Cartalyst\Sentry\Groups\GroupNotFoundException;
use Cartalyst\Sentry\Sentry;
use App\Repositories\Contracts\Invite;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Cartalyst\Sentry\Users\UserExistsException;
use Illuminate\Events\Dispatcher as Event;
use Exception;
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
    public function edit()
    {
        $id = $this->router->input('users');
        $user = $this->sentry->findUserById($id);

        if (is_null($user) || ! is_numeric($id)) {
            session_flash_push('error', trans('pages.error_missing_variable'));

            return false;
        }

        $timezones = timezone_select();
        $cancel = $this->url->route('projects.index');

        return compact('user', 'timezones', 'cancel');
    }

    public function update($request)
    {
        $result = $this->user->update($request->all());

        if ($result['success'])
        {
            session_flash_push('success', $result['message']);

            return true;
        }

        session_flash_push('error', $result['message']);

        return false;
    }

    /**
     * Add user to User Group.
     *
     * @param $user
     */
    public function addUserToUserGroup($user)
    {
        $usersGroup = $this->sentry->findGroupByName('Users');
        $user->addGroup($usersGroup);

        return;
    }

    /**
     * Add user by invite.
     *
     * @param $data
     * @param $user
     */
    public function addGroupByInvite($data, $user)
    {
        $invite = $this->invite->findByCode($data['invite']);
        if ($invite->email == $user->email) {
            $group = $this->sentry->findGroupById($invite->group_id);
            $user->addGroup($group);
            $this->invite->destroy($invite->id);

            return;
        }

        session_flash_push('warning', trans('groups.invite_email_mismatch'));

        return;
    }

    /**
     * Create user group based on email.
     *
     * @param $user
     */
    public function createGroupFromEmail($user)
    {
        $parts = explode("@", $user->email);
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $parts[0]);
        $userGroup = $this->sentry->createGroup([
            'user_id'     => $user->id,
            'name'        => $name,
            'permissions' => [],
        ]);

        $user->addGroup($userGroup);

        return;
    }
}