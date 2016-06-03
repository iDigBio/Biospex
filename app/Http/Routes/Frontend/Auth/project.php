<?php

// Begin ProjectsController
$router->get('projects', [
    'uses' => 'ProjectsController@index',
    'as'   => 'projects.get.index'
]);

$router->get('projects/create', [
    'uses' => 'ProjectsController@create',
    'as'   => 'projects.get.create'
]);

$router->post('projects/create', [
    'uses' => 'ProjectsController@store',
    'as'   => 'projects.post.store'
]);

$router->get('projects/{projects}', [
    'uses' => 'ProjectsController@show',
    'as'   => 'projects.get.show'
]);

$router->get('projects/{projects}/edit', [
    'uses' => 'ProjectsController@edit',
    'as'   => 'projects.get.edit'
]);

$router->put('projects/{projects}', [
    'uses' => 'ProjectsController@update',
    'as'   => 'projects.put.update'
]);

$router->delete('projects/{projects}', [
    'uses' => 'ProjectsController@delete',
    'as'   => 'projects.delete.delete'
]);

$router->get('projects/{projects}/duplicate', [
    'uses' => 'ProjectsController@duplicate',
    'as'   => 'projects.get.duplicate'
]);

$router->get('projects/{projects}/advertise', [
    'uses' => 'ProjectsController@advertise',
    'as'   => 'projects.get.advertise'
]);

$router->get('projects/{projects}/advertiseDownload', [
    'uses' => 'ProjectsController@advertiseDownload',
    'as'   => 'projects.get.advertiseDownload'
]);

$router->get('projects/{projects}/explore', [
    'uses' => 'ProjectsController@explore',
    'as'   => 'projects.get.explore'
]);
// End ProjectsController
