<?php

// Begin ProjectsController
$router->get('projects', [
    'uses' => 'ProjectsController@index',
    'as'   => 'admin.projects.index'
]);

$router->get('projects/{projects}/edit', [
    'uses' => 'ProjectsController@index',
    'as'   => 'admin.projects.edit'
]);

$router->put('projects/{projects}', [
    'uses' => 'ProjectsController@update',
    'as'   => 'admin.projects.update'
]);

$router->post('projects', [
    'uses' => 'ProjectsController@store',
    'as'   => 'admin.projects.store'
]);

$router->delete('projects/{projects}', [
    'uses' => 'ProjectsController@delete',
    'as'   => 'admin.projects.delete'
]);

$router->delete('projects/{projects}/destroy', [
    'uses' => 'ProjectsController@destroy',
    'as'   => 'admin.projects.destroy'
]);

$router->get('projects/{projects}/restore', [
    'uses' => 'ProjectsController@restore',
    'as'   => 'admin.projects.restore'
]);

// End ProjectsController
