<?php

// Begin UsersController
$router->get('users')->uses('UsersController@index')->name('admin.users.index');
$router->get('users/{users}/edit')->uses('UsersController@index')->name('admin.users.edit');
$router->put('users/{users}')->uses('UsersController@update')->name('admin.users.update');
$router->put('users/{users}')->uses('UsersController@update')->name('admin.users.update');
$router->put('users/{id}/pass')->uses('UsersController@pass')->name('admin.users.pass');
$router->get('users/search')->uses('UsersController@search')->name('admin.users.search');
$router->post('users')->uses('UsersController@store')->name('admin.users.store');
$router->delete('users/{users}')->uses('UsersController@delete')->name('admin.users.delete');
$router->delete('users/{users}/destroy')->uses('UsersController@destroy')->name('admin.users.destroy');
$router->get('users/{users}/restore')->uses('UsersController@restore')->name('admin.users.restore');
