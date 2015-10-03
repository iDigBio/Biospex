<?php

namespace app\Http\Middleware;

use Closure;

class Gate {

    protected $routeLogin = 'login';

    public function handle($request, Closure $next, $permissions) {
        $user = $request->user();

        // guest redirect
        if (is_null($user)) {
            return $request->ajax() ?
                response('Unauthorized.', 401) :
                redirect()->guest($this->routeLogin);
        }

        // strict permission check
        if ($user->cant('permit', [$permissions, true])) {
            abort(403);
        }

        return $next($request);
    }

}