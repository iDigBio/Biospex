<?php

namespace App\Listeners;

use App\Models\ApiUser;
use Illuminate\Auth\Events\Login;
use App\Repositories\Interfaces\Group;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Class GroupEventSubscriber
 *
 * @package App\Listeners
 */
class GroupEventSubscriber
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
            'App\Listeners\GroupEventSubscriber@onUserLogin'
        );

        $events->listen(
            Logout::class,
            'App\Listeners\GroupEventSubscriber@onUserLogout'
        );

        $events->listen(
            'group.saved',
            'App\Listeners\GroupEventSubscriber@setUserGroupSession'
        );

        $events->listen(
            'group.deleted',
            'App\Listeners\GroupEventSubscriber@setUserGroupSession'
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
     *
     * @param null $groupId
     */
    public function setUserGroupSession($groupId = null)
    {
        $groupIds = $this->groupContract->getUserGroupIds(Auth::id());

        $groups = $groupId === null ? $groupIds : $groupIds->diff([$groupId]);

        Session::put('groupIds', $groups->toArray());
    }
}
