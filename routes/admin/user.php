<?php

use Illuminate\Support\Facades\Route;

// Begin UsersController
Route::get('users')->uses('UsersController@index')->name('admin.users.index');
Route::get('users/{users}')->uses('UsersController@show')->name('admin.users.show');
Route::get('users/{users}/edit')->uses('UsersController@edit')->name('admin.users.edit');
Route::put('users/{users}')->uses('UsersController@update')->name('admin.users.update');
Route::put('password/{id}/pass')->uses('UsersController@pass')->name('admin.users.password');