<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Repositories\Contracts\GroupContract;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class GroupEventListener
{

    /**
     * @var GroupContract
     */
    private $groupContract;

    /**
     * GroupSessionEventListener constructor.
     *
     * @param GroupContract $groupContract
     */
    public function __construct(GroupContract $groupContract)
    {
        $this->groupContract = $groupContract;
    }

    /**
     * Handle user login events.
     */
    public function onUserLogin($event)
    {
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
     * Handle group saved event.
     */
    public function onGroupSaved()
    {
        $this->setUserGroupSession();
    }

    /**
     * Handle group deleted event.
     */
    public function onGroupDeleted()
    {
        $this->setUserGroupSession();
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
            'App\Listeners\GroupEventListener@onGroupSaved'
        );

        $events->listen(
            'group.deleted',
            'App\Listeners\GroupEventListener@onGroupDeleted'
        );

    }


    /**
     * Set the user groups inside a session variable.
     */
    public function setUserGroupSession()
    {
        $user = Auth::user();

        $groups = $this->groupContract->setCacheLifetime(0)
            ->whereHas('users', function ($query) use ($user)
            {
                $query->where('user_id', $user->id);
            })
            ->findAll();

        $uuids = $groups->map(function ($item, $key)
        {
            return $item['uuid'];
        });

        Session::put('user-groups', $uuids);
    }
}
