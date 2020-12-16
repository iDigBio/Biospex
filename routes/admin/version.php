<?php

use Illuminate\Support\Facades\Route;

Route::get('/version')->uses('VersionController@index')->name('admin.version.index');
Route::get('/version/show')->uses('VersionController@show')->name('admin.version.show');
Route::get('/version/create')->uses('VersionController@create')->name('admin.version.create');