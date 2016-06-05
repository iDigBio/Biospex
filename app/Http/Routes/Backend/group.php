<?php

// Begin GroupsController
$router->get('groups', [
    'uses' => 'GroupsController@index',
    'as'   => 'admin.groups.index'
]);

$router->get('groups/create', [
    'uses' => 'GroupsController@create',
    'as'   => 'admin.groups.create'
]);

$router->post('groups', [
    'uses' => 'GroupsController@store',
    'as'   => 'admin.groups.store'
]);

$router->get('groups/{groups}', [
    'uses' => 'GroupsController@show',
    'as'   => 'admin.groups.show'
]);

$router->get('groups/{groups}/edit', [
    'uses' => 'GroupsController@edit',
    'as'   => 'admin.groups.edit'
]);

$router->put('groups/{groups}', [
    'uses' => 'GroupsController@update',
    'as'   => 'admin.groups.update'
]);

$router->delete('groups/{groups}', [
    'uses' => 'GroupsController@delete',
    'as'   => 'admin.groups.delete'
]);
// End GroupsController