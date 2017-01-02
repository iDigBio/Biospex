<?php

// Begin UsersController
$router->get('users', [
    'uses' => 'UsersController@index',
    'as'   => 'admin.users.index'
]);

$router->get('users/{users}/edit', [
    'uses' => 'UsersController@index',
    'as'   => 'admin.users.edit'
]);

$router->put('users/{users}', [
    'uses' => 'UsersController@update',
    'as'   => 'admin.users.update'
]);

$router->put('users/{users}', [
    'uses' => 'UsersController@update',
    'as'   => 'admin.users.update'
]);

$router->put('users/{id}/pass', [
    'uses' => 'UsersController@pass',
    'as'   => 'admin.users.pass'
]);

$router->get('users/search', [
    'uses' => 'UsersController@search',
    'as'   => 'admin.users.search'
]);

$router->post('users', [
    'uses' => 'UsersController@store',
    'as'   => 'admin.users.store'
]);


$router->delete('users/{users}', [
    'uses' => 'UsersController@delete',
    'as'   => 'admin.users.delete'
]);

$router->delete('users/{users}/destroy', [
    'uses' => 'UsersController@destroy',
    'as'   => 'admin.users.destroy'
]);

$router->get('users/{users}/restore', [
    'uses' => 'UsersController@restore',
    'as'   => 'admin.users.restore'
]);
// End UsersController