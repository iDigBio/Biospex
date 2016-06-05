<?php

// Begin ProjectsController
$router->get('projects', [
    'uses' => 'ProjectsController@index',
    'as'   => 'admin.projects.index'
]);

$router->get('projects/create', [
    'uses' => 'ProjectsController@create',
    'as'   => 'admin.projects.create'
]);

$router->post('projects/create', [
    'uses' => 'ProjectsController@store',
    'as'   => 'admin.projects.store'
]);

$router->get('projects/{projects}', [
    'uses' => 'ProjectsController@show',
    'as'   => 'admin.projects.show'
]);

$router->get('projects/{projects}/edit', [
    'uses' => 'ProjectsController@edit',
    'as'   => 'admin.projects.edit'
]);

$router->put('projects/{projects}', [
    'uses' => 'ProjectsController@update',
    'as'   => 'admin.projects.update'
]);

$router->delete('projects/{projects}', [
    'uses' => 'ProjectsController@delete',
    'as'   => 'admin.projects.delete'
]);

// End ProjectsController
