<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserPolicy
{
    public function before($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        $access = Cache::remember($key, 60, function() use ($user) {
            return $user->isAdmin();
        });

        return $access ? true : null;
    }

    public function admin()
    {
        return false;
    }

    public function edit($user)
    {
        return $user === null ? false : Auth::getUser()->id === $user->id;
    }

    public function update($user)
    {
        return $user === null ? false : Auth::getUser()->id === $user->id;
    }

    public function pass($user)
    {
        return $user === null ? false : Auth::getUser()->id === $user->id;
    }

    public function delete($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        $access = Cache::remember($key, 60, function() use ($user) {
            return $user === null ? false : $user->isAdmin('superuser');
        });

        return $access ? true : null;
    }
}
