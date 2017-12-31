<?php

// Begin UsersController
$router->get('users')->uses('UsersController@index')->name('web.users.index');
$router->get('users/{users}')->uses('UsersController@show')->name('web.users.show');
$router->get('users/{users}/edit')->uses('UsersController@edit')->name('web.users.edit');
$router->put('users/{users}')->uses('UsersController@update')->name('web.users.update');
$router->delete('users/{users}')->uses('UsersController@destroy')->name('web.users.delete');

$router->put('password/{id}/pass')->uses('UsersController@pass')->name('web.users.password');
