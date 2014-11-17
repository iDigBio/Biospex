<?php
/**
 * filters.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
    //
});


App::after(function($request, $response)
{
    //
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/


Route::filter('auth', function()
{
    if (!Sentry::check()) return Redirect::guest('login');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	$token = Request::ajax() ? Request::header('X-CSRF-Token') : Input::get('_token');

    // TODO: Rewrite this tree of conditionals
    if (Session::token() !== $token || Session::token()===null || $token===null)
    {
        // Session token and form tokens do not match or one is empty
        if(App::environment() === 'testing')
        {
            // We only want to allow CSRF override if we're running tests
            if(Input::get('IgnoreCSRFTokenError')===true)
            {
                // Allow CSRF override in testing environment
                return;
            } else {
                // Handle CSRF normally
                throw new Illuminate\Session\TokenMismatchException;
            }
        } else {
            // @codeCoverageIgnoreStart

            // Handle CSRF normally
            throw new Illuminate\Session\TokenMismatchException;

            // @codeCoverageIgnoreEnd
        }
    }
});

/**
 * Protect Project pages
 */
Route::filter('hasProjectAccess', function($route, $request, $value)
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
    catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
    {
        Session::flash('error', trans('users.notfound'));
        return Redirect::guest('login');
    }
    catch (Cartalyst\Sentry\Groups\GroupNotFoundException $e)
    {
        Session::flash('error', trans('groups.notfound'));
        return Redirect::guest('login');
    }
});

Route::filter('hasGroupAccess', function($route, $request, $value)
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
    catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
    {
        Session::flash('error', trans('users.notfound'));
        return Redirect::guest('login');
    }
    catch (Cartalyst\Sentry\Groups\GroupNotFoundException $e)
    {
        Session::flash('error', trans('groups.notfound'));
        return Redirect::guest('login');
    }
});

Route::filter('hasUserAccess', function($route, $request, $value)
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
    catch (Cartalyst\Sentry\Users\UserNotFoundException $e)
    {
        Session::flash('error', trans('users.notfound'));
        return Redirect::guest('login');
    }

    catch (Cartalyst\Sentry\Groups\GroupNotFoundException $e)
    {
        Session::flash('error', trans('groups.notfound'));
        return Redirect::guest('login');
    }
});