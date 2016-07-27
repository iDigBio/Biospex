<?php

// Begin ResourcesController
$router->get('resources', [
    'uses' => 'ResourcesController@index',
    'as'   => 'admin.resources.index'
]);

$router->get('resources/create', [
    'uses' => 'ResourcesController@create',
    'as'   => 'admin.resources.create'
]);

$router->post('resources/create', [
    'uses' => 'ResourcesController@store',
    'as'   => 'admin.resources.store'
]);

$router->get('resources/{resources}', [
    'uses' => 'ResourcesController@show',
    'as'   => 'admin.resources.show'
]);

$router->get('resources/{resources}/edit', [
    'uses' => 'ResourcesController@edit',
    'as'   => 'admin.resources.edit'
]);

$router->put('resources/{resources}', [
    'uses' => 'ResourcesController@update',
    'as'   => 'admin.resources.update'
]);

$router->delete('resources/{resources}', [
    'uses' => 'ResourcesController@delete',
    'as'   => 'admin.resources.delete'
]);

$router->delete('resources/{resources}/trash', [
    'uses' => 'ResourcesController@trash',
    'as'   => 'admin.resources.trash'
]);

$router->post('resources/{resources}/order/{order}', [
    'uses' => 'ResourcesController@order',
    'as'   => 'admin.resources.order'
]);

// End ResourcesController
