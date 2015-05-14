<?php namespace Biospex\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Cartalyst\Sentry\Users\UserNotFoundException;
use Cartalyst\Sentry\Groups\GroupNotFoundException;
use Cartalyst\Sentry\Sentry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Local;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'Biospex\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function boot(Router $router)
	{
		parent::boot($router);

        $router->bind('locale', Local::setLocale());

        /**
         * Protect Project pages
         */
        $router->filter('hasProjectAccess', function($route, $request, $value)
        {
            try
            {
                $user = Sentry::getUser();

                // Super user has all permissions
                if ($user->isSuperUser())
                    return;

                $id = $route->getParameter('projects');
                if (empty($id))
                {
                    Session::flash('error', trans('users.noaccess'));
                    return Redirect::intended('/');
                }

                $projectKey = md5('project.' . $id);
                if (Cache::tags('queries')->has($projectKey))
                {
                    $project = Cache::tags('queries')->get($projectKey);
                } else
                {
                    $project = Project::find($id);
                    Cache::tags('queries')->forever($projectKey, $project);
                }

                $groupId = $project->group_id;
                $groupKey = md5('group.' . $groupId);
                if (Cache::tags('queries')->has($groupKey))
                {
                    $group = Cache::tags('queries')->get($groupKey);
                } else
                {
                    $group = Sentry::findGroupById($groupId);
                    Cache::tags('queries')->forever($groupKey, $group);
                }

                if ($user->inGroup($group) && $user->hasAccess(array($value)))
                    return;

                Session::flash('error', trans('users.noaccess'));
                return Redirect::intended('/');
            }
            catch (UserNotFoundException $e)
            {
                Session::flash('error', trans('users.notfound'));
                return Redirect::guest('login');
            }
            catch (GroupNotFoundException $e)
            {
                Session::flash('error', trans('groups.notfound'));
                return Redirect::guest('login');
            }
        });

        $router->filter('hasGroupAccess', function($route, $request, $value)
        {
            try
            {
                $user = Sentry::getUser();
                $id = $route->getParameter('groups');

                if ($user->isSuperUser())
                    return;

                if (empty($id) && $user->hasAccess(array($value)))
                    return;

                if ($id)
                {
                    $groupKey = "group.$id";
                    if (Cache::tags('queries')->has($groupKey))
                    {
                        $group = Cache::tags('queries')->get($groupKey);
                    } else
                    {
                        $group = Sentry::findGroupById($id);
                        Cache::tags('queries')->forever($groupKey, $group);
                    }

                    if ($group->user_id == $user->id)
                        return;
                    if ($user->inGroup($group) && ($value != 'group_edit' || $value != 'group_delete'))
                        return;
                }

                Session::flash('error', trans('users.noaccess'));
                return Redirect::intended('/');
            }
            catch (UserNotFoundException $e)
            {
                Session::flash('error', trans('users.notfound'));
                return Redirect::guest('login');
            }
            catch (GroupNotFoundException $e)
            {
                Session::flash('error', trans('groups.notfound'));
                return Redirect::guest('login');
            }
        });

        $router->filter('hasUserAccess', function($route, $request, $value)
        {
            try
            {
                $user = Sentry::getUser();
                $userId = $route->getParameter('users');

                if ($user->id == $userId) return;

                if (!$user->hasAccess(array($value)))
                {
                    Session::flash('error', trans('users.noaccess'));
                    return Redirect::intended('/');
                }
            }
            catch (UserNotFoundException $e)
            {
                Session::flash('error', trans('users.notfound'));
                return Redirect::guest('login');
            }

            catch (GroupNotFoundException $e)
            {
                Session::flash('error', trans('groups.notfound'));
                return Redirect::guest('login');
            }
        });
	}

	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(['namespace' => $this->namespace], function($router)
		{
			require app_path('Http/routes.php');
		});
	}

}
