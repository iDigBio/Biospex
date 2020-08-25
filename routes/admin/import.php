<?php

use Illuminate\Support\Facades\Route;

Route::get('/import')->uses('ImportController@index')->name('admin.import.index');
Route::post('/import')->uses('ImportController@create')->name('admin.import.create');
Route::put('/update')->uses('ImportController@update')->name('admin.import.update');
Route::get('/update/select')->uses('ImportController@select')->name('admin.import.select');
Route::put('/update/select')->uses('ImportController@selected')->name('admin.import.selected');
