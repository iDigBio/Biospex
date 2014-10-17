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
	[
        'prefix' => Local::setLocale(),
        'before' => 'LocalRedirectFilter'
	],
    function () {

		Route::get('phpinfo', ['as' => 'phpinfo', 'uses' => 'ServerInfoController@showPhpInfo']);
		Route::get('opcache', ['as' => 'opcache', 'uses' => 'ServerInfoController@showOpCache']);
		Route::get('test', ['as' => 'test', 'uses' => 'ServerInfoController@test']);

        // Session Routes
		Route::get('login', ['as' => 'login', 'uses' => 'SessionsController@create']);
		Route::get('logout', ['as' => 'logout', 'uses' => 'SessionsController@destroy']);
		Route::resource('sessions', 'SessionsController', ['only' => ['create', 'store', 'destroy']]);

        // User Routes
		Route::get('register/{code?}', ['as' => 'register', 'uses' => 'UsersController@register']);
		Route::get('users/{id}/activate/{code}', ['as' => 'activate', 'uses' => 'UsersController@activate']);
		Route::get('resend', ['as' => 'resendActivationForm', function ()
		{
            return View::make('users.resend');
		}]);
		Route::post('resend', ['as' => 'resend', 'uses' => 'UsersController@resend']);
		Route::get('forgot', ['as' => 'forgotPasswordForm', function ()
		{
            return View::make('users.forgot');
		}]);
		Route::post('forgot', ['as' => 'forgot', 'uses' => 'UsersController@forgot']);
		Route::post('users/{id}/change', ['as' => 'change', 'uses' => 'UsersController@change']);
		Route::get('users/{id}/reset/{code}', ['as' => 'reset', 'uses' => 'UsersController@reset']);
		Route::get('users/{id}/suspend', ['as' => 'suspendUserForm', function ($id)
		{
            return View::make('users.suspend')->with('id', $id);
		}]);
		Route::post('users/{id}/suspend', ['as' => 'suspend', 'uses' => 'UsersController@suspend']);
		Route::get('users/{id}/unsuspend', ['as' => 'unsuspend', 'uses' => 'UsersController@unsuspend']);
		Route::get('users/{id}/ban', ['as' => 'ban', 'uses' => 'UsersController@ban']);
		Route::get('users/{id}/unban', ['as' => 'unban', 'uses' => 'UsersController@unban']);
        Route::resource('users', 'UsersController');

        // Group Routes
        Route::resource('groups', 'GroupsController');

        // Group invite routes
		Route::post('groups/{groups}/invites/{invites}/resend', ['as' => 'groups.invites.resend', 'uses' => 'InvitesController@resend']);
        Route::resource('groups.invites', 'InvitesController');

        // Group/Projects
        Route::resource('projects', 'ProjectsController');
		Route::get('projects/{projects}/duplicate', ['as' => 'projects.duplicate', 'uses' => 'ProjectsController@duplicate']);
		Route::get('projects/{projects}/data', ['as' => 'projects.data', 'uses' => 'ProjectsController@data']);
		Route::post('projects/{projects}/data', ['as' => 'projects.upload', 'uses' => 'ProjectsController@upload']);
		Route::get('projects/{projects}/advertise', ['as' => 'projects.advertise', 'uses' => 'ProjectsController@advertise']);

        // Group/Projects/Expeditions
        Route::resource('projects.expeditions', 'ExpeditionsController');
		Route::get('projects/{projects}/expeditions/{expeditions}/duplicate', ['as' => 'projects.expeditions.duplicate', 'uses' => 'ExpeditionsController@duplicate']);
		Route::get('projects/{projects}/expeditions/{expeditions}/process', ['as' => 'projects.expeditions.process', 'uses' => 'ExpeditionsController@process']);
		Route::delete('projects/{projects}/expeditions/{expeditions}/stop', ['as' => 'projects.expeditions.stop', 'uses' => 'ExpeditionsController@stop']);
		Route::get('projects/{projects}/expeditions/{expeditions}/download', ['as' => 'projects.expeditions.download', 'uses' => 'ExpeditionsController@download']);
		Route::get('projects/{projects}/expeditions/{expeditions}/file/{id}', ['as' => 'projects.expeditions.file', 'uses' => 'ExpeditionsController@file']);

		Route::get('grids/{expedition}', ['as' => 'grid-index', 'uses' => 'GridsController@index']);
		Route::post('grids/{expedition}/grid-data', ['as' => 'grid-data', 'uses' => 'GridsController@index']);

		Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);
		Route::get('help', ['as' => 'help', 'uses' => 'HomeController@help']);
		Route::get('/{slug}', ['as' => 'project', 'uses' => 'HomeController@project']);

    });

/*
+--------+----------------------------------------------------------------------+--------------------------------+------------------------------------------------------------+---------------------+---------------+
| Domain | URI                                                                  | Name                           | Action                                                     | Before Filters      | After Filters |
+--------+----------------------------------------------------------------------+--------------------------------+------------------------------------------------------------+---------------------+---------------+
|        | GET|HEAD assets/{path}                                               |                                | \Codesleeve\AssetPipeline\AssetPipelineController@file     |                     |               |
|        | GET|HEAD _debugbar/open                                              | debugbar.openhandler           | Barryvdh\Debugbar\Controllers\OpenHandlerController@handle |                     |               |
|        | GET|HEAD _debugbar/assets/stylesheets                                | debugbar.assets.css            | Barryvdh\Debugbar\Controllers\AssetController@css          |                     |               |
|        | GET|HEAD _debugbar/assets/javascript                                 | debugbar.assets.js             | Barryvdh\Debugbar\Controllers\AssetController@js           |                     |               |
|        | GET|HEAD phpinfo                                                     | phpinfo                        | PhpInfoController@show                                     | LocalRedirectFilter |               |
|        | GET|HEAD test                                                        | test                           | PhpInfoController@test                                     | LocalRedirectFilter |               |
|        | GET|HEAD login                                                       | login                          | SessionsController@create                                  | LocalRedirectFilter |               |
|        | GET|HEAD logout                                                      | logout                         | SessionsController@destroy                                 | LocalRedirectFilter |               |
|        | GET|HEAD sessions/create                                             | sessions.create                | SessionsController@create                                  | LocalRedirectFilter |               |
|        | POST sessions                                                        | sessions.store                 | SessionsController@store                                   | LocalRedirectFilter |               |
|        | DELETE sessions/{sessions}                                           | sessions.destroy               | SessionsController@destroy                                 | LocalRedirectFilter |               |
|        | GET|HEAD register/{code?}                                            | register                       | UsersController@register                                   | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/activate/{code}                                  |                                | UsersController@activate                                   | LocalRedirectFilter |               |
|        | GET|HEAD resend                                                      | resendActivationForm           | Closure                                                    | LocalRedirectFilter |               |
|        | POST resend                                                          |                                | UsersController@resend                                     | LocalRedirectFilter |               |
|        | GET|HEAD forgot                                                      | forgotPasswordForm             | Closure                                                    | LocalRedirectFilter |               |
|        | POST forgot                                                          |                                | UsersController@forgot                                     | LocalRedirectFilter |               |
|        | POST users/{id}/change                                               |                                | UsersController@change                                     | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/reset/{code}                                     |                                | UsersController@reset                                      | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/suspend                                          | suspendUserForm                | Closure                                                    | LocalRedirectFilter |               |
|        | POST users/{id}/suspend                                              |                                | UsersController@suspend                                    | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/unsuspend                                        |                                | UsersController@unsuspend                                  | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/ban                                              |                                | UsersController@ban                                        | LocalRedirectFilter |               |
|        | GET|HEAD users/{id}/unban                                            |                                | UsersController@unban                                      | LocalRedirectFilter |               |
|        | GET|HEAD users                                                       | users.index                    | UsersController@index                                      | LocalRedirectFilter |               |
|        | GET|HEAD users/create                                                | users.create                   | UsersController@create                                     | LocalRedirectFilter |               |
|        | POST users                                                           | users.store                    | UsersController@store                                      | LocalRedirectFilter |               |
|        | GET|HEAD users/{users}                                               | users.show                     | UsersController@show                                       | LocalRedirectFilter |               |
|        | GET|HEAD users/{users}/edit                                          | users.edit                     | UsersController@edit                                       | LocalRedirectFilter |               |
|        | PUT users/{users}                                                    | users.update                   | UsersController@update                                     | LocalRedirectFilter |               |
|        | PATCH users/{users}                                                  |                                | UsersController@update                                     | LocalRedirectFilter |               |
|        | DELETE users/{users}                                                 | users.destroy                  | UsersController@destroy                                    | LocalRedirectFilter |               |
|        | GET|HEAD groups                                                      | groups.index                   | GroupsController@index                                     | LocalRedirectFilter |               |
|        | GET|HEAD groups/create                                               | groups.create                  | GroupsController@create                                    | LocalRedirectFilter |               |
|        | POST groups                                                          | groups.store                   | GroupsController@store                                     | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}                                             | groups.show                    | GroupsController@show                                      | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/edit                                        | groups.edit                    | GroupsController@edit                                      | LocalRedirectFilter |               |
|        | PUT groups/{groups}                                                  | groups.update                  | GroupsController@update                                    | LocalRedirectFilter |               |
|        | PATCH groups/{groups}                                                |                                | GroupsController@update                                    | LocalRedirectFilter |               |
|        | DELETE groups/{groups}                                               | groups.destroy                 | GroupsController@destroy                                   | LocalRedirectFilter |               |
|        | POST groups/{groups}/invites/{invites}/resend                        | groups.invites.resend          | InvitesController@resend                                   | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/invites                                     | groups.invites.index           | InvitesController@index                                    | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/invites/create                              | groups.invites.create          | InvitesController@create                                   | LocalRedirectFilter |               |
|        | POST groups/{groups}/invites                                         | groups.invites.store           | InvitesController@store                                    | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/invites/{invites}                           | groups.invites.show            | InvitesController@show                                     | LocalRedirectFilter |               |
|        | GET|HEAD groups/{groups}/invites/{invites}/edit                      | groups.invites.edit            | InvitesController@edit                                     | LocalRedirectFilter |               |
|        | PUT groups/{groups}/invites/{invites}                                | groups.invites.update          | InvitesController@update                                   | LocalRedirectFilter |               |
|        | PATCH groups/{groups}/invites/{invites}                              |                                | InvitesController@update                                   | LocalRedirectFilter |               |
|        | DELETE groups/{groups}/invites/{invites}                             | groups.invites.destroy         | InvitesController@destroy                                  | LocalRedirectFilter |               |
|        | GET|HEAD projects                                                    | projects.index                 | ProjectsController@index                                   | LocalRedirectFilter |               |
|        | GET|HEAD projects/create                                             | projects.create                | ProjectsController@create                                  | LocalRedirectFilter |               |
|        | POST projects                                                        | projects.store                 | ProjectsController@store                                   | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}                                         | projects.show                  | ProjectsController@show                                    | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/edit                                    | projects.edit                  | ProjectsController@edit                                    | LocalRedirectFilter |               |
|        | PUT projects/{projects}                                              | projects.update                | ProjectsController@update                                  | LocalRedirectFilter |               |
|        | PATCH projects/{projects}                                            |                                | ProjectsController@update                                  | LocalRedirectFilter |               |
|        | DELETE projects/{projects}                                           | projects.destroy               | ProjectsController@destroy                                 | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/duplicate                               | projects.duplicate             | ProjectsController@duplicate                               | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/data                                    | projects.data                  | ProjectsController@data                                    | LocalRedirectFilter |               |
|        | POST projects/{projects}/data                                        | projects.upload                | ProjectsController@upload                                  | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/advertise                               | projects.advertise             | ProjectsController@advertise                               | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/expeditions                             | projects.expeditions.index     | ExpeditionsController@index                                | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/expeditions/create                      | projects.expeditions.create    | ExpeditionsController@create                               | LocalRedirectFilter |               |
|        | POST projects/{projects}/expeditions                                 | projects.expeditions.store     | ExpeditionsController@store                                | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/expeditions/{expeditions}               | projects.expeditions.show      | ExpeditionsController@show                                 | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/expeditions/{expeditions}/edit          | projects.expeditions.edit      | ExpeditionsController@edit                                 | LocalRedirectFilter |               |
|        | PUT projects/{projects}/expeditions/{expeditions}                    | projects.expeditions.update    | ExpeditionsController@update                               | LocalRedirectFilter |               |
|        | PATCH projects/{projects}/expeditions/{expeditions}                  |                                | ExpeditionsController@update                               | LocalRedirectFilter |               |
|        | DELETE projects/{projects}/expeditions/{expeditions}                 | projects.expeditions.destroy   | ExpeditionsController@destroy                              | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/expeditions/{expeditions}/duplicate     | projects.expeditions.duplicate | ExpeditionsController@duplicate                            | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/expeditions/{expeditions}/process       | projects.expeditions.process   | ExpeditionsController@process                              | LocalRedirectFilter |               |
|        | POST projects/{projects}/expeditions/{expeditions}/stop              | projects.expeditions.stop      | ExpeditionsController@stop                                 | LocalRedirectFilter |               |
|        | GET|HEAD projects/{projects}/expeditions/{expeditions}/download/{id} | projects.expeditions.download  | ExpeditionsController@download                             | LocalRedirectFilter |               |
|        | GET|HEAD grids/{expedition}                                          | grid-index                     | GridsController@index                                      | LocalRedirectFilter |               |
|        | POST grids/{expedition}/grid-data                                    | grid-data                      | GridsController@index                                      | LocalRedirectFilter |               |
|        | GET|HEAD /                                                           | home                           | HomeController@index                                       | LocalRedirectFilter |               |
|        | GET|HEAD {slug}                                                      | project                        | HomeController@project                                     | LocalRedirectFilter |               |
+--------+----------------------------------------------------------------------+--------------------------------+------------------------------------------------------------+---------------------+---------------+

*/