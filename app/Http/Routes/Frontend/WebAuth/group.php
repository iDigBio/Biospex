<?php

// Begin GroupsController
$router->get('groups', [
    'uses' => 'GroupsController@index',
    'as'   => 'groups.get.index'
]);

$router->get('groups/create', [
    'uses' => 'GroupsController@create',
    'as'   => 'groups.get.create'
]);

$router->post('groups', [
    'uses' => 'GroupsController@store',
    'as'   => 'groups.post.store'
]);

$router->get('groups/{groups}', [
    'uses' => 'GroupsController@show',
    'as'   => 'groups.get.show'
]);

$router->get('groups/{groups}/edit', [
    'uses' => 'GroupsController@edit',
    'as'   => 'groups.get.edit'
]);

$router->put('groups/{groups}', [
    'uses' => 'GroupsController@update',
    'as'   => 'groups.put.update'
]);

$router->delete('groups/{groups}', [
    'uses' => 'GroupsController@delete',
    'as'   => 'groups.delete.delete'
]);
// End GroupsController