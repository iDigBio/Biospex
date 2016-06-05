<?php
// Begin UsersController
$router->get('users', [
    'uses' => 'UsersController@index',
    'as'   => 'web.users.index'
]);

$router->get('users/{users}', [
    'uses' => 'UsersController@show',
    'as'   => 'web.users.show'
]);

$router->get('users/{users}/edit', [
    'uses' => 'UsersController@edit',
    'as'   => 'web.users.edit'
]);

$router->put('users/{users}', [
    'uses' => 'UsersController@update',
    'as'   => 'web.users.update'
]);

$router->delete('users/{users}', [
    'uses' => 'UsersController@destroy',
    'as'   => 'web.users.delete'
]);
// End UsersController