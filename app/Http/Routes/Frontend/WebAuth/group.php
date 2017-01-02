<?php

// Begin GroupsController
$router->get('groups', [
    'uses' => 'GroupsController@index',
    'as'   => 'web.groups.index'
]);

$router->get('groups/create', [
    'uses' => 'GroupsController@create',
    'as'   => 'web.groups.create'
]);

$router->post('groups', [
    'uses' => 'GroupsController@store',
    'as'   => 'web.groups.store'
]);

$router->get('groups/{groups}', [
    'uses' => 'GroupsController@show',
    'as'   => 'web.groups.show'
]);

$router->get('groups/{groups}/edit', [
    'uses' => 'GroupsController@edit',
    'as'   => 'web.groups.edit'
]);

$router->put('groups/{groups}', [
    'uses' => 'GroupsController@update',
    'as'   => 'web.groups.update'
]);

$router->delete('groups/{groups}', [
    'uses' => 'GroupsController@delete',
    'as'   => 'web.groups.delete'
]);

$router->delete('groups/{groups}/destroy', [
    'uses' => 'GroupsController@destroy',
    'as'   => 'web.groups.destroy'
]);

$router->get('groups/{groups}/restore', [
    'uses' => 'GroupsController@restore',
    'as'   => 'web.groups.restore'
]);

// End GroupsController