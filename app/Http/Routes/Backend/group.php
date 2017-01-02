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

$router->put('groups/{groups}', [
    'uses' => 'GroupsController@update',
    'as'   => 'admin.groups.update'
]);

$router->delete('groups/{groups}', [
    'uses' => 'GroupsController@delete',
    'as'   => 'admin.groups.delete'
]);

$router->delete('groups/{groups}/destroy', [
    'uses' => 'GroupsController@destroy',
    'as'   => 'admin.groups.destroy'
]);

$router->get('groups/{groups}/restore', [
    'uses' => 'GroupsController@restore',
    'as'   => 'admin.groups.restore'
]);

$router->post('groups/{groups}/invite', [
    'uses' => 'GroupsController@invite',
    'as'   => 'admin.groups.invite'
]);

$router->delete('groups/{groups}/user/{user}', [
    'uses' => 'GroupsController@deleteUser',
    'as'   => 'admin.groups.deleteUser'
]);
// End GroupsController