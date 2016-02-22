<?php

namespace App\Http\Middleware;

use Closure;

class Admin
{
    /**
     * Handle authorization
     * @param $request
     * @param Closure $next
     * @param $ability
     * @param null $model
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('dashboard.get.index');
        }

        return $next($request);
    }

}
