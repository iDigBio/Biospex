<?php

namespace App\Http\Middleware;

use Closure;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Groups\GroupNotFoundException;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Project;

class SentryInGroup
{
    private $project;
    private $group;

    public function __construct(Group $group, Project $project)
    {
        $this->group = $group;
        $this->project = $project;
    }

    /**
     * Sentry - Check if user is in group/groups
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $group = null;

        $actions = $request->route()->getAction();

        if ($request->route()->hasParameter('groups')) {
            $group = $this->group->find($request->route()->getParameter('groups'));
        }

        if ($request->route()->hasParameter('projects')) {
            $project = $this->project->findWith($request->route()->getParameter('projects'), ['group']);
        }

        $group = is_null($group) ? $project->group : $group;

        if (array_key_exists("inGroup", $actions)) {
            try {
                $user = \Sentry::getUser();

                if ((! $user->isSuperUser()) && (! $user->inGroup($group))) {
                    return redirect()->route('home')->with('error', trans('acl.cannot_reach_this_resource_with_your_role'));
                }
            } catch (UserNotFoundException $e) {
                return redirect()->route('login')->with('error', trans('acl.user_not_found'));
            } catch (GroupNotFoundException $e) {
                return redirect()->route('login')->with('error', trans('acl.group_not_found'));
            }
        }

        return $next($request);
    }
}
