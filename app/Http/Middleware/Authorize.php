<?php

namespace App\Http\Middleware;

use Closure;

class Authorize
{

    /**
     * Create a new filter instance.
     *
     * @param Group $group
     * @param Project $project
     * @param Expedition $expedition
     * @internal param Guard $auth
     * @internal param Access $access
     * @internal param Gate $gate
     * @internal param Guard $auth
     */
    public function __construct()
    {

    }

    /**
     * Handle authorization
     * @param $request
     * @param Closure $next
     * @param $ability
     * @param null $model
     * @return mixed
     */
    public function handle($request, Closure $next, $ability = null, $model = null)
    {
        if ($request->user()->can($ability, [$model, $request])) {
            return $next($request);
        }

        session_flash_push('warning', trans('pages.insufficient_permissions'));

        return $request->ajax ? response('Unauthorized.', 401) : redirect()->back();
    }

}
