<?php

namespace App\Policies;

use Illuminate\Support\Facades\Cache;

class EventPolicy
{

    /**
     * Allow admins.
     *
     * @param $user
     * @return bool|null
     */
    public function before($user)
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Is owner of event.
     *
     * @param $user
     * @param $event
     * @return bool
     */
    public function isOwner($user, $event)
    {
        return $user->id === $event->ower_id;
    }

    /**
     * Check if user can create event
     * @param $user
     * @return bool
     */
    public function create($user)
    {
        return true;
    }

    /**
     * Check if user can store event
     * @param $user
     * @return bool
     */
    public function store($user)
    {
        return true;
    }

    /**
     * Check if user can read event.
     *
     * @param $user
     * @param $event
     * @return bool|string
     */
    public function read($user, $event)
    {
        return $this->isOwner($user, $event);
    }

    /**
     * Check if user can update event
     * @param $user
     * @param $event
     * @return mixed
     */
    public function update($user, $event)
    {
        return $this->isOwner($user, $event);
    }

    /**
     * Check if user can delete event
     * @param $user
     * @param $event
     * @return bool
     */
    public function delete($user, $event)
    {
        return $this->isOwner($user, $event);
    }
}
