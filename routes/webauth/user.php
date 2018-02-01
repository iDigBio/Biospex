<?php

// Begin UsersController
$router->get('users')->uses('UsersController@index')->name('webauth.users.index');
$router->get('users/{users}')->uses('UsersController@show')->name('webauth.users.show');
$router->get('users/{users}/edit')->uses('UsersController@edit')->name('webauth.users.edit');
$router->put('users/{users}')->uses('UsersController@update')->name('webauth.users.update');
$router->delete('users/{users}')->uses('UsersController@destroy')->name('webauth.users.delete');

$router->put('password/{id}/pass')->uses('UsersController@pass')->name('webauth.users.password');
