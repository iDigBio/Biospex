<?php

use Illuminate\Support\Facades\Route;

Route::get('/import')->uses('ImportController@index')->name('admin.get.import');
Route::post('/import')->uses('ImportController@create')->name('admin.import.create');
Route::put('/import')->uses('ImportController@update')->name('admin.import.update');