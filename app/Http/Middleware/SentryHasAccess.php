<?php

namespace App\Http\Middleware;

use Closure;
use Cartalyst\Sentry\Users\UserNotFoundException;

class SentryHasAccess
{
    /**
     * Sentry - Check role permission
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $actions = $request->route()->getAction();

        $userId = null;
        if ($request->route()->hasParameter('users')) {
            $userId = $request->route()->getParameter('users');
        }

        if (array_key_exists('hasAccess', $actions)) {
            try {
                $user = \Sentry::getUser();

                $permission =  $user->hasAccess($actions['hasAccess']) ? true : ($userId == $user->id) ? true : false;

                if (! $permission) {
                    return redirect()->route('home')->with('warning', trans('acl.you_dont_have_permission_for_this_resource'));
                }
            } catch (UserNotFoundException $e) {
                return redirect()->route('login')->with('warning', trans('acl.user_not_found'));
            }
        }

        return $next($request);
    }
}
