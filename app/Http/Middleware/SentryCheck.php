<?php

namespace App\Http\Middleware;

use Closure;
use Cartalyst\Sentry\Facades\Laravel\Sentry;

class SentryCheck
{
    /**
     * Sentry - Check login status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Sentry::check()) {
            return $next($request);
        }

        return redirect()->guest('login');
    }
}
