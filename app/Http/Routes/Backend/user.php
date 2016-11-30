<?php

// Begin UsersController
$router->get('users', [
    'uses' => 'UsersController@index',
    'as'   => 'admin.users.index'
]);

$router->get('users/search', [
    'uses' => 'UsersController@search',
    'as'   => 'admin.users.search'
]);

$router->get('users/create', [
    'uses' => 'UsersController@create',
    'as'   => 'admin.users.create'
]);

$router->post('users', [
    'uses' => 'UsersController@store',
    'as'   => 'admin.users.store'
]);

$router->get('users/{users}', [
    'uses' => 'UsersController@show',
    'as'   => 'admin.users.show'
]);

$router->put('users/{users}', [
    'uses' => 'UsersController@update',
    'as'   => 'admin.users.update'
]);

$router->delete('users/{users}', [
    'uses' => 'UsersController@delete',
    'as'   => 'admin.users.delete'
]);

$router->delete('users/{users}/trash', [
    'uses' => 'UsersController@trash',
    'as'   => 'admin.users.trash'
]);

$router->get('users/{users}/restore', [
    'uses' => 'UsersController@restore',
    'as'   => 'admin.users.restore'
]);
// End UsersController