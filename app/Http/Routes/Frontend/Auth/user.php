<?php
// Begin UsersController
$router->get('users', [
    'uses' => 'UsersController@index',
    'as'   => 'users.get.index'
]);

$router->get('users/{users}', [
    'uses' => 'UsersController@show',
    'as'   => 'users.get.show'
]);

$router->get('users/{users}/edit', [
    'uses' => 'UsersController@edit',
    'as'   => 'users.get.edit'
]);

$router->put('users/{users}', [
    'uses' => 'UsersController@update',
    'as'   => 'users.put.update'
]);

$router->delete('users/{users}', [
    'uses' => 'UsersController@destroy',
    'as'   => 'users.delete.delete'
]);
// End UsersController