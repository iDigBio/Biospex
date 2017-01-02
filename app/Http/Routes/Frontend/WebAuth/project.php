<?php

// Begin ProjectsController
$router->get('projects', [
    'uses' => 'ProjectsController@index',
    'as'   => 'web.projects.index'
]);

$router->get('projects/create', [
    'uses' => 'ProjectsController@create',
    'as'   => 'web.projects.create'
]);

$router->post('projects/create', [
    'uses' => 'ProjectsController@store',
    'as'   => 'web.projects.store'
]);

$router->get('projects/{projects}', [
    'uses' => 'ProjectsController@show',
    'as'   => 'web.projects.show'
]);

$router->get('projects/{projects}/edit', [
    'uses' => 'ProjectsController@edit',
    'as'   => 'web.projects.edit'
]);

$router->put('projects/{projects}', [
    'uses' => 'ProjectsController@update',
    'as'   => 'web.projects.update'
]);

$router->delete('projects/{projects}', [
    'uses' => 'ProjectsController@delete',
    'as'   => 'web.projects.delete'
]);

$router->delete('projects/{projects}/destroy', [
    'uses' => 'ProjectsController@destroy',
    'as'   => 'web.projects.destroy'
]);

$router->get('projects/{projects}/restore', [
    'uses' => 'ProjectsController@restore',
    'as'   => 'web.projects.restore'
]);

$router->get('projects/{projects}/duplicate', [
    'uses' => 'ProjectsController@duplicate',
    'as'   => 'web.projects.duplicate'
]);

$router->get('projects/{projects}/explore', [
    'uses' => 'ProjectsController@explore',
    'as'   => 'projects.get.explore'
]);

$router->get('projects/{projects}/ocr', [
    'uses' => 'ProjectsController@ocr',
    'as'   => 'web.projects.ocr'
]);
// End ProjectsController
