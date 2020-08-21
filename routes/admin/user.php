<?php

use Illuminate\Support\Facades\Route;

// Begin UserController
Route::get('users')->uses('UserController@index')->name('admin.users.index');
Route::get('users/{users}')->uses('UserController@show')->name('admin.users.show');
Route::get('users/{users}/edit')->uses('UserController@edit')->name('admin.users.edit');
Route::put('users/{users}')->uses('UserController@update')->name('admin.users.update');
Route::put('password/{id}/pass')->uses('UserController@pass')->name('admin.users.password');