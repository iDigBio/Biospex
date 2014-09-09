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

        Route::get('phpinfo', array('as' => 'phpinfo', 'uses' => 'PhpInfoController@show'));
        Route::get('test', array('as' => 'test', 'uses' => 'PhpInfoController@test'));

        // Session Routes
        Route::get('login', array('as' => 'login', 'uses' => 'SessionsController@create'));
        Route::get('logout', array('as' => 'logout', 'uses' => 'SessionsController@destroy'));
        Route::resource('sessions', 'SessionsController', array('only' => array('create', 'store', 'destroy')));

        // User Routes
        Route::get('register/{code?}', array('as' => 'register', 'uses' => 'UsersController@register'));
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

        // Group Routes
        Route::resource('groups', 'GroupsController');

        // Group invite routes
        Route::post('groups/{groups}/invites/{invites}/resend', array('as' => 'groups.invites.resend', 'uses' => 'InvitesController@resend'));
        Route::resource('groups.invites', 'InvitesController');

        // Group/Projects
        Route::resource('projects', 'ProjectsController');
        Route::get('projects/{projects}/duplicate', array('as' => 'projects.duplicate', 'uses' => 'ProjectsController@duplicate'));
        Route::get('projects/{projects}/data', array('as' => 'projects.data', 'uses' => 'ProjectsController@data'));
        Route::post('projects/{projects}/data', array('as' => 'projects.upload', 'uses' => 'ProjectsController@upload'));
        Route::get('projects/{projects}/advertise', array('as' => 'projects.advertise', 'uses' => 'ProjectsController@advertise'));

        // Group/Projects/Expeditions
        Route::resource('projects.expeditions', 'ExpeditionsController');
        Route::get('projects/{projects}/expeditions/{expeditions}/duplicate', array('as' => 'projects.expeditions.duplicate', 'uses' => 'ExpeditionsController@duplicate'));
        Route::get('projects/{projects}/expeditions/{expeditions}/process', array('as' => 'projects.expeditions.process', 'uses' => 'ExpeditionsController@process'));
		Route::get('projects/{projects}/expeditions/{expeditions}/stop', array('as' => 'projects.expeditions.stop', 'uses' => 'ExpeditionsController@stop'));
        Route::get('projects/{projects}/expeditions/{expeditions}/download/{id}', array('as' => 'projects.expeditions.download', 'uses' => 'ExpeditionsController@download'));

        Route::get('grids/{expedition}', array('as' => 'grid-index', 'uses' => 'GridsController@index'));
        Route::post('grids/{expedition}/grid-data', array('as' => 'grid-data', 'uses' => 'GridsController@index'));

        Route::get('/', array('as' => 'home', 'uses' => 'HomeController@index'));
        Route::get('/{slug}', array('as' => 'project', 'uses' => 'HomeController@project'));

    });

/*

*/