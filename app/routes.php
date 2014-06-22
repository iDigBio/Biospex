<?php
/**
 * routes.php
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

// Route Patterns
Route::pattern('id', '[0-9]+');

/** ADD ALL LOCALIZED ROUTES INSIDE THIS GROUP **/
Route::group(
    array(
        'prefix' => Local::setLocale(),
        'before' => 'LocalRedirectFilter'
    ),
    function () {

        Route::get('/', array('as' => 'home', function () {
            return View::make('home');
        }));

        Route::get('phpinfo', array('as' => 'phpinfo', 'uses' => 'PhpInfoController@show'));

        // Session Routes
        Route::get('login', array('as' => 'login', 'uses' => 'SessionsController@create'));
        Route::get('logout', array('as' => 'logout', 'uses' => 'SessionsController@destroy'));
        Route::resource('sessions', 'SessionsController', array('only' => array('create', 'store', 'destroy')));

        // User Routes
        Route::get('register', array('as' => 'register', 'uses' => 'UsersController@register'));
        Route::get('users/{id}/activate/{code}', 'UsersController@activate');
        Route::get('resend', array('as' => 'resendActivationForm', function () {
            return View::make('users.resend');
        }));
        Route::post('resend', 'UsersController@resend');
        Route::get('forgot', array('as' => 'forgotPasswordForm', function () {
            return View::make('users.forgot');
        }));
        Route::post('forgot', 'UsersController@forgot');
        Route::post('users/{id}/change', 'UsersController@change');
        Route::get('users/{id}/reset/{code}', 'UsersController@reset');
        Route::get('users/{id}/suspend', array('as' => 'suspendUserForm', function ($id) {
            return View::make('users.suspend')->with('id', $id);
        }));
        Route::post('users/{id}/suspend', 'UsersController@suspend');
        Route::get('users/{id}/unsuspend', 'UsersController@unsuspend');
        Route::get('users/{id}/ban', 'UsersController@ban');
        Route::get('users/{id}/unban', 'UsersController@unban');
        Route::resource('users', 'UsersController');

        // Show all projects user belongs to by group
        Route::get('projects/all', 'ProjectsController@all');

        // Group Routes
        Route::get('groups/dropdown', array('as' => 'dropdown', 'uses' => 'GroupsController@dropdown'));
        Route::resource('groups', 'GroupsController');

        // Group/Projects
        Route::resource('groups.projects', 'ProjectsController');
        Route::get('groups/{groups}/projects/{projects}/duplicate', array('as' => 'project-dup', 'uses' => 'ProjectsController@duplicate'));
        Route::get('groups/{groups}/projects/{projects}/data', array('as' => 'addData', 'uses' => 'ProjectsController@data'));
        Route::post('groups/{groups}/projects/{projects}/upload', array('as' => 'dataUpload', 'uses' => 'ProjectsController@upload'));

        // Group/Projects/Expeditions
        Route::resource('groups.projects.expeditions', 'ExpeditionsController');
        Route::get('groups/{groups}/projects/{projects}/expeditions/{expeditions}/duplicate', array('as' => 'expedition-dup', 'uses' => 'ExpeditionsController@duplicate'));
        Route::get('groups/{groups}/projects/{projects}/expeditions/{expeditions}/process', array('as' => 'process', 'uses' => 'ExpeditionsController@process'));

        // Project routes
        /*
        GET	/resource	index	resource.index
        GET	/resource/create	create	resource.create
        POST	/resource	store	resource.store
        GET	/resource/{resource}	show	resource.show
        GET	/resource/{resource}/edit	edit	resource.edit
        PUT/PATCH	/resource/{resource}	update	resource.update
        DELETE	/resource/{resource}	destroy	resource.destroy
         */
    });

/** OTHER PAGES THAT SHOULD NOT BE LOCALIZED **/
/*
+--------+-----------------------------------------------------------------------------+-------------------------------------+-------------------------------+---------------------+---------------+
| Domain | URI                                                                         | Name                                | Action                        | Before Filters      | After Filters |
+--------+-----------------------------------------------------------------------------+-------------------------------------+-------------------------------+---------------------+---------------+
|        | GET|HEAD /                                                                  | home                                | Closure                       | LocalRedirectFilter |               |
|        | GET|HEAD login                                                              | login                               | SessionsController@create     | LocalRedirectFilter |               |
|        | GET|HEAD logout                                                             | logout                              | SessionsController@destroy    | LocalRedirectFilter |               |
|        | GET|HEAD sessions/create                                                    | sessions.create                     | SessionsController@create     | LocalRedirectFilter |               |
|        | POST sessions                                                               | sessions.store                      | SessionsController@store      | LocalRedirectFilter |               |
|        | DELETE sessions/{sessions}                                                  | sessions.destroy                    | SessionsController@destroy    | LocalRedirectFilter |               |
|        | GET|HEAD register                                                           | register                            | UsersController@register      | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/activate/{code}                                         |                                     | UsersController@activate      | LocalRedirectFilter |               |
|        | GET|HEAD resend                                                             | resendActivationForm                | Closure                       | LocalRedirectFilter |               |
|        | POST resend                                                                 |                                     | UsersController@resend        | LocalRedirectFilter |               |
|        | GET|HEAD forgot                                                             | forgotPasswordForm                  | Closure                       | LocalRedirectFilter |               |
|        | POST forgot                                                                 |                                     | UsersController@forgot        | LocalRedirectFilter |               |
|        | POST users/{id}/change                                                      |                                     | UsersController@change        | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/reset/{code}                                            |                                     | UsersController@reset         | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/suspend                                                 | suspendUserForm                     | Closure                       | LocalRedirectFilter |               |
|        | POST users/{id}/suspend                                                     |                                     | UsersController@suspend       | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/unsuspend                                               |                                     | UsersController@unsuspend     | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/ban                                                     |                                     | UsersController@ban           | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/unban                                                   |                                     | UsersController@unban         | LocalRedirectFilter |               |
|        | GET|HEAD users                                                              | users.index                         | UsersController@index         | LocalRedirectFilter |               |
|        | GET|HEAD users/create                                                       | users.create                        | UsersController@create        | LocalRedirectFilter |               |
|        | POST users                                                                  | users.store                         | UsersController@store         | LocalRedirectFilter |               |
|        | GET|HEAD users/{users}                                                      | users.show                          | UsersController@show          | LocalRedirectFilter |               |
|        | GET|HEAD users/{users}/edit                                                 | users.edit                          | UsersController@edit          | LocalRedirectFilter |               |
|        | PUT users/{users}                                                           | users.update                        | UsersController@update        | LocalRedirectFilter |               |
|        | PATCH users/{users}                                                         |                                     | UsersController@update        | LocalRedirectFilter |               |
|        | DELETE users/{users}                                                        | users.destroy                       | UsersController@destroy       | LocalRedirectFilter |               |
|        | GET|HEAD groups                                                             | groups.index                        | GroupsController@index        | LocalRedirectFilter |               |
|        | GET|HEAD groups/create                                                      | groups.create                       | GroupsController@create       | LocalRedirectFilter |               |
|        | POST groups                                                                 | groups.store                        | GroupsController@store        | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}                                                    | groups.show                         | GroupsController@show         | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/edit                                               | groups.edit                         | GroupsController@edit         | LocalRedirectFilter |               |
|        | PUT groups/{groups}                                                         | groups.update                       | GroupsController@update       | LocalRedirectFilter |               |
|        | PATCH groups/{groups}                                                       |                                     | GroupsController@update       | LocalRedirectFilter |               |
|        | DELETE groups/{groups}                                                      | groups.destroy                      | GroupsController@destroy      | LocalRedirectFilter |               |
|        | GET|HEAD groups/{group}/projects                                            | groups.projects.index                | ProjectsController@index      | LocalRedirectFilter |               |
|        | GET|HEAD groups/{group}/projects/create                                     | groups.projects.create               | ProjectsController@create     | LocalRedirectFilter |               |
|        | POST groups/{group}/projects                                                | groups.projects.store                | ProjectsController@store      | LocalRedirectFilter |               |
|        | GET|HEAD groups/{group}/projects/{projects}                                 | groups.projects.show                 | ProjectsController@show       | LocalRedirectFilter |               |
|        | GET|HEAD groups/{group}/projects/{projects}/edit                            | groups.projects.edit                 | ProjectsController@edit       | LocalRedirectFilter |               |
|        | PUT groups/{group}/projects/{projects}                                      | groups.projects.update               | ProjectsController@update     | LocalRedirectFilter |               |
|        | PATCH groups/{group}/projects/{projects}                                    |                                     | ProjectsController@update     | LocalRedirectFilter |               |
|        | DELETE groups/{group}/projects/{projects}                                   | groups.projects.destroy              | ProjectsController@destroy    | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/projects/{projects}/expeditions                    | groups.projects.expeditions.index   | ExpeditionsController@index   | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/projects/{projects}/expeditions/create             | groups.projects.expeditions.create  | ExpeditionsController@create  | LocalRedirectFilter |               |
|        | POST groups/{groups}/projects/{projects}/expeditions                        | groups.projects.expeditions.store   | ExpeditionsController@store   | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/projects/{projects}/expeditions/{expeditions}      | groups.projects.expeditions.show    | ExpeditionsController@show    | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/projects/{projects}/expeditions/{expeditions}/edit | groups.projects.expeditions.edit    | ExpeditionsController@edit    | LocalRedirectFilter |               |
|        | PUT groups/{groups}/projects/{projects}/expeditions/{expeditions}           | groups.projects.expeditions.update  | ExpeditionsController@update  | LocalRedirectFilter |               |
|        | PATCH groups/{groups}/projects/{projects}/expeditions/{expeditions}         |                                     | ExpeditionsController@update  | LocalRedirectFilter |               |
|        | DELETE groups/{groups}/projects/{projects}/expeditions/{expeditions}        | groups.projects.expeditions.destroy | ExpeditionsController@destroy | LocalRedirectFilter |               |
+--------+-----------------------------------------------------------------------------+-------------------------------------+-------------------------------+---------------------+---------------+
*/