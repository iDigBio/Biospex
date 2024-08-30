<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Listeners;

use App\Services\Models\GroupModelService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Class GroupEventSubscriber
 *
 * @package App\Listeners
 */
readonly class GroupEventSubscriber
{
    /**
     * GroupSessionEventListener constructor.
     *
     */
    public function __construct(private GroupModelService $groupModelService)
    {}

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
     *
     * @param $event
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
     * Set the user groups inside a session variable.
     *
     * @param null $groupId
     */
    public function setUserGroupSession($groupId = null): void
    {
        $groupIds = $this->groupModelService->getUserGroupIds(Auth::id());

        $groups = $groupId === null ? $groupIds : $groupIds->diff([$groupId]);

        Session::put('groupIds', $groups->toArray());
    }
}
