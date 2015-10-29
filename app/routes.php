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
/*
Route::group(
    [
        'domain' => Config::get('config.api.domain'),
    ],
    function () {
        Route::get('/', ['as' => 'api.index', 'uses' => 'Api\\ApiController@index']);
        Route::get('downloads/{id}', ['as' => 'apidownloads.get.show', 'uses' => 'Api\\ApiDownloadsController@show']);
});
*/
/** ADD ALL LOCALIZED ROUTES INSIDE THIS GROUP **/
Route::group(
    [
        //'domain' => Config::get('config.site.domain'),
        'prefix' => Local::setLocale(),
        'before' => 'LocalRedirectFilter'
    ],
    function () {

        Route::get('test', ['as' => 'get.test', 'uses' => 'ServerInfoController@getTest']);
        Route::post('test', ['as' => 'post.test', 'uses' => 'ServerInfoController@postTest']);
        Route::get('phpinfo', ['as' => 'phpinfo', 'uses' => 'ServerInfoController@showPhpInfo']);
        Route::get('clear', ['as' => 'clear', 'uses' => 'ServerInfoController@clear']);
        Route::match(['GET', 'POST'], 'ocr', 'ServerInfoController@ocr');

        // Session Routes
        Route::get('login', ['as' => 'login', 'uses' => 'SessionsController@create']);
        Route::get('logout', ['as' => 'logout', 'uses' => 'SessionsController@destroy']);
        Route::resource('sessions', 'SessionsController', ['only' => ['create', 'store', 'destroy']]);

        // User Routes
        Route::get('register/{code?}', ['as' => 'register', 'uses' => 'UsersController@register']);
        Route::get('users/{id}/activate/{code}', ['as' => 'activate', 'uses' => 'UsersController@activate']);
        Route::get('resend', ['as' => 'resendActivationForm', function () {
            return View::make('users.resend');
        }]);
        Route::post('resend', ['as' => 'resend', 'uses' => 'UsersController@resend']);
        Route::get('forgot', ['as' => 'forgotPasswordForm', function () {
            return View::make('users.forgot');
        }]);
        Route::post('forgot', ['as' => 'forgot', 'uses' => 'UsersController@forgot']);
        Route::post('users/{id}/change', ['as' => 'change', 'uses' => 'UsersController@change']);
        Route::get('users/{id}/reset/{code}', ['as' => 'reset', 'uses' => 'UsersController@reset']);
        Route::get('users/{id}/suspend', ['as' => 'suspendUserForm', function ($id) {
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

        // Projects
        Route::resource('projects', 'ProjectsController');
        Route::get('projects/{projects}/duplicate', ['as' => 'projects.duplicate', 'uses' => 'ProjectsController@duplicate']);
        Route::get('projects/{projects}/advertise', ['as' => 'projects.advertise', 'uses' => 'ProjectsController@advertise']);
        Route::get('projects/{projects}/advertiseDownload', ['as' => 'projects.advertiseDownload', 'uses' => 'ProjectsController@advertiseDownload']);

        // Projects/Expeditions
        Route::resource('projects.expeditions', 'ExpeditionsController');
        Route::get('projects/{projects}/expeditions/{expeditions}/duplicate', ['as' => 'projects.expeditions.duplicate', 'uses' => 'ExpeditionsController@duplicate']);
        Route::get('projects/{projects}/expeditions/{expeditions}/process', ['as' => 'projects.expeditions.process', 'uses' => 'ExpeditionsController@process']);
        Route::get('projects/{projects}/expeditions/{expeditions}/ocr', ['as' => 'projects.expeditions.ocr', 'uses' => 'ExpeditionsController@ocr']);
        Route::delete('projects/{projects}/expeditions/{expeditions}/stop', ['as' => 'projects.expeditions.stop', 'uses' => 'ExpeditionsController@stop']);

        // Projects/Expeditions/Downloads
        Route::resource('projects.expeditions.downloads', 'DownloadsController');

        // Project Imports
        Route::get('projects/{projects}/import', ['as' => 'projects.import', 'uses' => 'ImportsController@import']);
        Route::post('projects/{projects}/import', ['as' => 'projects.upload', 'uses' => 'ImportsController@upload']);

        // Projects/Expeditions/Grids
        Route::get('projects/{projects}/subjects', ['as' => 'projects.subjects', 'uses' => 'SubjectsController@index']);
        Route::get('projects/{projects}/subjects/load', ['as' => 'projects.subjects.load', 'uses' => 'SubjectsController@load']);
        Route::get('projects/{projects}/subjects/{expeditions}', ['as' => 'projects.subjects.show', 'uses' => 'SubjectsController@show']);
        Route::post('projects/{projects}/subjects/{expeditions}', ['as' => 'projects.subjects.store', 'uses' => 'SubjectsController@store']);

        // ImagesController
        Route::get('images/html', ['as' => 'images.html', 'uses' => 'ImagesController@html']);
        Route::get('images/preview', ['as' => 'images.preview', 'uses' => 'ImagesController@preview']);

        Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);
        Route::get('help', ['as' => 'help', 'uses' => 'HomeController@help']);
        Route::get('project/{slug}', ['as' => 'project', 'uses' => 'HomeController@project']);

        // Contact form
        Route::get('contact', ['as' => 'contact', 'uses' => 'HomeController@contact']);
        Route::post('contact', ['as' => 'contact.send', 'uses' => 'HomeController@sendContactForm']);

    });
