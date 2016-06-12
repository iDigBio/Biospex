<?php

namespace App\Policies;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserPolicy
{
    public function before($user)
    {
        $key = md5(__METHOD__ . $user->uuid);
        $isAdmin = Cache::remember($key, 60, function() use ($user) {
            return $user->isAdmin();
        });

        if ($isAdmin) {
            return true;
        }
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
        return Cache::remember($key, 60, function() use ($user) {
            return $user === null ? false : $user->isAdmin('superuser');
        });
    }
}
