<?php

namespace App\Http\Middleware;

use Closure;

class Admin
{

    /**
     * Handle is user is not in admin group
     * @param $request
     * @param Closure $next
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if ( ! $request->user()->isAdmin()) {
            return redirect()->route('webauth.projects.index');
        }

        return $next($request);
    }

}
