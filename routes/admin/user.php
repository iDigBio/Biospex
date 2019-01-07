<?php

// Begin UsersController
$router->get('users')->uses('UsersController@index')->name('admin.users.index');
$router->get('users/{users}')->uses('UsersController@show')->name('admin.users.show');
$router->get('users/{users}/edit')->uses('UsersController@edit')->name('admin.users.edit');
$router->put('users/{users}')->uses('UsersController@update')->name('admin.users.update');
$router->delete('users/{users}')->uses('UsersController@destroy')->name('admin.users.delete');

$router->put('password/{id}/pass')->uses('UsersController@pass')->name('admin.users.password');
