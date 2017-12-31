<?php

namespace App\Listeners;

use App\Models\ApiUser;
use Illuminate\Auth\Events\Login;
use App\Interfaces\Group;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class GroupEventListener
{

    /**
     * @var Group
     */
    private $groupContract;

    /**
     * GroupSessionEventListener constructor.
     *
     * @param Group $groupContract
     */
    public function __construct(Group $groupContract)
    {
        $this->groupContract = $groupContract;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            Login::class,
            'App\Listeners\GroupEventListener@onUserLogin'
        );

        $events->listen(
            Logout::class,
            'App\Listeners\GroupEventListener@onUserLogout'
        );

        $events->listen(
            'group.saved',
            'App\Listeners\GroupEventListener@setUserGroupSession'
        );

        $events->listen(
            'group.deleted',
            'App\Listeners\GroupEventListener@setUserGroupSession'
        );

    }

    /**
     * Handle user login events.
     */
    public function onUserLogin($event)
    {
        if ($event->user instanceof ApiUser)
        {
            return;
        }

        $this->setUserGroupSession();
    }

    /**
     * Handle user logout.
     *
     * @param $event
     */
    public function onUserLogout($event)
    {
        Session::flush();
    }

    /**
     * Set the user groups inside a session variable.
     */
    public function setUserGroupSession()
    {
        $uuids = $this->groupContract->getUserGroupUuids(Auth::id());

        Session::put('groupUuids', $uuids);
    }
}
