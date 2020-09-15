<?php

use Illuminate\Support\Facades\Route;

Route::get('/export')->uses('ExportController@index')->name('admin.export.index');
Route::post('/export/geolocate')->uses('ExportController@geolocate')->name('admin.export.geolocate');
Route::post('/export/geolocate/create')->uses('ExportController@geolocateCreate')->name('admin.export.geolocate.create');
Route::post('/export/geolocate/form')->uses('ExportController@form')->name('admin.export.geolocate.form');