<?php

// Route Patterns
Route::pattern('id', '[0-9]+');

/** ADD ALL LOCALIZED ROUTES INSIDE THIS GROUP **/
Route::group(
    [
        'middleware' => ['web'],
        'namespace' => 'Front',
        'prefix'    => Local::setLocale(),
        'before'    => 'LocalRedirectFilter'
    ],
    function () {
        // Begin AuthController
        Route::get('/login', [
            'uses' => 'AuthController@getLogin',
            'as'   => 'auth.get.login',
            //'middleware' => 'doNotCacheResponse'
        ]);

        Route::post('/login', [
            'uses' => 'AuthController@postLogin',
            'as'   => 'auth.post.login',
            //'middleware' => 'doNotCacheResponse'
        ]);

        Route::get('/logout', [
            'uses' => 'AuthController@getLogout',
            'as'   => 'auth.get.logout',
            //'middleware' => 'doNotCacheResponse'
        ]);

        Route::get('register/{code?}', [
            'uses' => 'AuthController@getRegister',
            'as'   => 'auth.get.register',
            //'middleware' => 'doNotCacheResponse'
        ]);

        Route::post('register', [
            'uses' => 'AuthController@postRegister',
            'as'   => 'auth.post.register',
            //'middleware' => 'doNotCacheResponse'
        ]);

        Route::get('/users/{id}/activate/{code}', [
            'uses' => 'AuthController@getActivate',
            'as'   => 'auth.get.activate'
        ]);

        Route::get('resend', [
            'uses' => 'AuthController@getResendActivation',
            'as'   => 'auth.get.resend'
        ]);

        Route::post('resend', [
            'uses' => 'AuthController@postResendActivation',
            'as'   => 'auth.post.resend'
        ]);
        // End AuthController

        // Begin PasswordController
        Route::get('password/email', [
            'uses' => 'PasswordController@getEmail',
            'as'   => 'password.get.email'
        ]);

        Route::post('password/email', [
            'uses' => 'PasswordController@postEmail',
            'as'   => 'password.post.email'
        ]);

        Route::get('password/reset/{token}', [
            'uses' => 'PasswordController@getReset',
            'as'   => 'password.get.reset'
        ]);

        Route::post('password/reset', [
            'uses' => 'PasswordController@postReset',
            'as'   => 'password.post.reset'
        ]);
        // End PasswordsController

        // Begin UsersController
        Route::post('users/{id}/change', [
            'uses'       => 'UsersController@change',
            'as'         => 'users.pass',
            'middleware' => 'sentry'
        ]);

        Route::get('users', [
            'uses'       => 'UsersController@index',
            'as'         => 'users.index',
            'middleware' => ['sentry', 'hasAccess'],
            'hasAccess'  => 'superuser'
        ]);

        Route::get('users/create', [
            'uses'       => 'UsersController@create',
            'as'         => 'users.create',
            'middleware' => ['sentry', 'hasAccess'],
            'hasAccess'  => 'superuser'
        ]);

        Route::post('users/{users}', [
            'uses'       => 'UsersController@show',
            'as'         => 'users.show',
            'middleware' => ['sentry', 'hasAccess'],
            'hasAccess'  => 'superuser'
        ]);

        Route::get('users/{users}/edit', [
            'uses'       => 'UsersController@edit',
            'as'         => 'users.edit',
            'middleware' => ['sentry', 'hasAccess'],
            'hasAccess'  => 'superuser'
        ]);

        Route::put('users/{users}', [
            'uses'       => 'UsersController@update',
            'as'         => 'users.update',
            'middleware' => ['sentry', 'hasAccess'],
            'hasAccess'  => 'superuser'
        ]);

        Route::delete('users/{users}', [
            'uses'       => 'UsersController@destroy',
            'as'         => 'users.destroy',
            'middleware' => ['sentry', 'hasAccess'],
            'hasAccess'  => 'superuser'
        ]);
        // End UsersController

        // Begin GroupsController
        Route::get('groups', [
            'uses'       => 'GroupsController@index',
            'as'         => 'groups.get.index',
            'middleware' => ['auth'],
        ]);

        Route::get('groups/create', [
            'uses'       => 'GroupsController@create',
            'as'         => 'groups.get.create',
            'middleware' => ['auth'],
        ]);

        Route::post('groups', [
            'uses'       => 'GroupsController@store',
            'as'         => 'groups.post.store',
            'middleware' => ['auth'],
        ]);

        Route::get('groups/{groups}', [
            'uses'       => 'GroupsController@read',
            'as'         => 'groups.get.read',
            'middleware' => ['auth', 'acl:read,App\Models\Group'],
        ]);

        Route::get('groups/{groups}/edit', [
            'uses'       => 'GroupsController@edit',
            'as'         => 'groups.get.edit',
            'middleware' => ['auth', 'acl:update,App\Models\Group'],
        ]);

        Route::put('groups/{groups}', [
            'uses'       => 'GroupsController@update',
            'as'         => 'groups.put.update',
            'middleware' => ['auth', 'acl:update,App\Models\Group'],
        ]);

        Route::delete('groups/{groups}', [
            'uses'       => 'GroupsController@delete',
            'as'         => 'groups.delete.delete',
            'middleware' => ['auth', 'acl:delete,App\Models\Group'],
        ]);
        // End GroupsController

        // Group invite routes
        Route::get('groups/{groups}/invites', [
            'uses'       => 'InvitesController@index',
            'as'         => 'invites.get.index',
            'middleware' => ['auth', 'acl:update,App\Models\Group'],
        ]);

        Route::post('groups/{groups}/invites', [
            'uses'       => 'InvitesController@store',
            'as'         => 'invites.post.store',
            'middleware' => ['auth', 'acl:update,App\Models\Group'],
        ]);

        Route::post('groups/{groups}/invites/{invites}/resend', [
            'uses'       => 'InvitesController@resend',
            'as'         => 'invites.post.resend',
            'middleware' => ['auth']
        ]);

        Route::delete('groups/{groups}/invites/{invites}', [
            'uses'       => 'InvitesController@delete',
            'as'         => 'invites.delete.delete',
            'middleware' => ['auth'],
        ]);

        // Begin ProjectsController
        Route::get('dashboard', [
            'uses'       => 'ProjectsController@index',
            'as'         => 'projects.get.index',
            'middleware' => ['auth']
        ]);

        Route::get('projects/create', [
            'uses'       => 'ProjectsController@create',
            'as'         => 'projects.get.create',
            'middleware' => ['auth'],
        ]);

        Route::post('projects/create', [
            'uses'       => 'ProjectsController@store',
            'as'         => 'projects.post.store',
            'middleware' => ['auth'],
        ]);

        Route::get('projects/{projects}', [
            'uses'       => 'ProjectsController@read',
            'as'         => 'projects.get.read',
            'middleware' => ['auth'],
        ]);

        Route::get('projects/{projects}/edit', [
            'uses'       => 'ProjectsController@edit',
            'as'         => 'projects.get.edit',
            'middleware' => ['auth'],
        ]);

        Route::put('projects/{projects}', [
            'uses'       => 'ProjectsController@update',
            'as'         => 'projects.put.update',
            'middleware' => ['auth'],
        ]);

        Route::delete('projects/{projects}', [
            'uses'       => 'ProjectsController@delete',
            'as'         => 'projects.delete.delete',
            'middleware' => ['auth'],
        ]);

        Route::get('projects/{projects}/duplicate', [
            'uses'       => 'ProjectsController@duplicate',
            'as'         => 'projects.duplicate',
            'middleware' => ['auth'],
        ]);

        Route::get('projects/{projects}/advertise', [
            'uses'       => 'ProjectsController@advertise',
            'as'         => 'projects.advertise',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'project_view'
        ]);

        Route::get('projects/{projects}/advertiseDownload', [
            'uses'       => 'ProjectsController@advertiseDownload',
            'as'         => 'projects.advertiseDownload',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'project_view'
        ]);
        // End ProjectsController

        Route::get('projects/{projects}/import', [
            'uses'       => 'ImportsController@import',
            'as'         => 'projects.import',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'hasAccess'  => 'project_edit'
        ]);

        Route::post('projects/{projects}/import', [
            'uses'       => 'ImportsController@upload',
            'as'         => 'projects.upload',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'hasAccess'  => 'project_edit'
        ]);

        Route::get('projects/{projects}/expeditions', [
            'uses'       => 'ExpeditionsController@index',
            'as'         => 'projects.expeditions.index',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_view'
        ]);

        Route::get('projects/{projects}/expeditions/create', [
            'uses'       => 'ExpeditionsController@create',
            'as'         => 'projects.expeditions.create',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_create'
        ]);

        Route::post('projects/{projects}/expeditions', [
            'uses'       => 'ExpeditionsController@store',
            'as'         => 'projects.expeditions.store',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_create'
        ]);

        Route::get('projects/{projects}/expeditions/{expeditions}', [
            'uses'       => 'ExpeditionsController@show',
            'as'         => 'projects.expeditions.show',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'hasAccess'  => 'expedition_view'
        ]);

        Route::get('projects/{projects}/expeditions/{expeditions}/edit', [
            'uses'       => 'ExpeditionsController@edit',
            'as'         => 'projects.expeditions.edit',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_edit'
        ]);

        Route::put('projects/{projects}/expeditions/{expeditions}', [
            'uses'       => 'ExpeditionsController@update',
            'as'         => 'projects.expeditions.update',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_edit'
        ]);

        Route::delete('projects/{projects}/expeditions/{expeditions}', [
            'uses'       => 'ExpeditionsController@destroy',
            'as'         => 'projects.expeditions.destroy',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_delete'
        ]);

        Route::get('projects/{projects}/expeditions/{expeditions}/duplicate', [
            'uses'       => 'ExpeditionsController@duplicate',
            'as'         => 'projects.expeditions.duplicate',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_create'
        ]);

        Route::get('projects/{projects}/expeditions/{expeditions}/process', [
            'uses'       => 'ExpeditionsController@process',
            'as'         => 'projects.expeditions.process',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'hasAccess'  => 'expedition_edit'
        ]);

        Route::delete('projects/{projects}/expeditions/{expeditions}/stop', [
            'uses'       => 'ExpeditionsController@stop',
            'as'         => 'projects.expeditions.stop',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'hasAccess'  => 'expedition_edit'
        ]);

        Route::get('projects/{projects}/expeditions/{expeditions}/downloads', [
            'uses'       => 'DownloadsController@index',
            'as'         => 'projects.expeditions.downloads.index',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'hasAccess'  => 'expedition_view'
        ]);

        Route::get('projects/{projects}/expeditions/{expeditions}/ocr', [
            'uses'       => 'ExpeditionsController@ocr',
            'as'         => 'projects.expeditions.ocr',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'hasAccess'  => 'expedition_edit'
        ]);

        Route::get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}', [
            'uses' => 'DownloadsController@show',
            'as'   => 'projects.expeditions.downloads.show',
        ]);

        // Projects/Expeditions/Subjects
        Route::get('projects/{projects}/subjects', [
            'uses'       => 'SubjectsController@index',
            'as'         => 'projects.subjects',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_view'
        ]);

        Route::get('projects/{projects}/subjects/load', [
            'uses'       => 'SubjectsController@load',
            'as'         => 'projects.subjects.load',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'hasAccess'  => 'expedition_view'
        ]);

        Route::get('projects/{projects}/subjects/{expeditions}', [
            'uses'       => 'SubjectsController@show',
            'as'         => 'projects.subjects.show',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_view'
        ]);

        Route::post('projects/{projects}/subjects/{expeditions}', [
            'uses'       => 'SubjectsController@store',
            'as'         => 'projects.subjects.store',
            'middleware' => ['sentry', 'inGroup', 'hasAccess'],
            'inGroup'    => '{project_id}',
            'hasAccess'  => 'expedition_create'
        ]);

        // ImagesController
        Route::get('images/html', [
            'uses' => 'ImagesController@html',
            'as'   => 'images.html'
        ]);

        Route::get('images/preview', [
            'uses' => 'ImagesController@preview',
            'as'   => 'images.preview'
        ]);

        // Contact form
        Route::get('contact', [
            'uses' => 'HomeController@getContact',
            'as'   => 'home.get.contact',
            //'middleware' => 'doNotCacheResponse'
        ]);

        Route::post('contact', [
            'uses' => 'HomeController@postContact',
            'as'   => 'home.post.contact',
            //'middleware' => 'doNotCacheResponse'
        ]);

        // Home and Welcome
        Route::get('/', [
            'uses' => 'HomeController@index',
            'as'   => 'home'
        ]);

        Route::get('help', [
            'uses' => 'HomeController@help',
            'as'   => 'home.get.help'
        ]);

        Route::get('project/{slug}', [
            'uses' => 'HomeController@project',
            'as'   => 'home.get.project'
        ]);
    }
);


/*
Route::group(['namespace' => 'Admin', 'prefix' => 'admin'], function () {
    Route::get('test', [
        'uses'       => 'ServerInfoController@getTest',
        'as'         => 'get.test',
        'middleware' => ['sentry', 'hasAccess'],
        'hasAccess'  => 'superuser'
    ]);

    Route::post('test', [
        'uses'       => 'ServerInfoController@postTest',
        'as'         => 'post.test',
        'middleware' => ['sentry', 'hasAccess'],
        'hasAccess'  => 'superuser'
    ]);

    Route::get('phpinfo', [
        'uses'       => 'ServerInfoController@showPhpInfo',
        'as'         => 'phpinfo',
        'middleware' => ['sentry', 'hasAccess'],
        'hasAccess'  => 'superuser'
    ]);

    Route::get('clear', [
        'uses'       => 'ServerInfoController@clear',
        'as'         => 'clear',
        'middleware' => ['sentry', 'hasAccess'],
        'hasAccess'  => 'superuser'
    ]);

    Route::match(['GET', 'POST'], 'ocr', 'ServerInfoController@ocr');

    Route::get('/', [
        'uses'       => 'DashboardController@index',
        'as'         => 'dashboard',
        'middleware' => ['admin', 'hasAccess'],
        'hasAccess'  => 'superuser'
    ]);

    Route::get('/login', [
        'uses' => 'AuthController@login',
        'as'   => 'admin.login'
    ]);

    Route::post('/login', [
        'uses' => 'AuthController@store',
        'as'   => 'admin.store'
    ]);

    Route::get('/logout', [
        'uses' => 'AuthController@destroy',
        'as'   => 'admin.logout'
    ]);

    Route::get('/password', [
        'uses' => 'AuthController@password',
        'as'   => 'admin.password'
    ]);

    Route::post('/forgot', [
        'uses' => 'AuthController@forgot',
        'as'   => 'admin.forgot'
    ]);

    Route::post('/reset', [
        'uses' => 'AuthController@rest',
        'as'   => 'admin.reset'
    ]);

    Route::get('/users/{id}/suspend', [
        'as'         => 'suspendUserForm',
        'middleware' => 'sentry',
        function ($id) {
            return view('users.suspend')->with('id', $id);
        }
    ]);

    Route::post('/users/{id}/suspend', [
        'uses' => 'UsersController@suspend',
        'as'   => 'suspend'
    ]);

    Route::get('/users/{id}/unsuspend', [
        'uses' => 'UsersController@unsuspend',
        'as'   => 'unsuspend'
    ]);

    Route::get('/users/{id}/ban', [
        'uses' => 'UsersController@ban',
        'as'   => 'ban'
    ]);

    Route::get('users/{id}/unban', [
        'uses' => 'UsersController@unban',
        'as'   => 'unban'
    ]);

});
*/

Route::group([
    'middleware' => ['api'],
    'namespace' => 'Api',
    'prefix' => 'api'],
    function () {
        Route::resource('api', 'ApiController');
});
