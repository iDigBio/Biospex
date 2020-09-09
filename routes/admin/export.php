<?php

use Illuminate\Support\Facades\Route;

Route::get('/export')->uses('ExportController@index')->name('admin.export.index');
Route::get('/export/geolocate')->uses('ExportController@geolocate')->name('admin.export.geolocate');
Route::post('/export/geolocate')->uses('ExportController@geolocateCreate')->name('admin.export.geolocate.post');