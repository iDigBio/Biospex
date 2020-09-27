<?php

use Illuminate\Support\Facades\Route;

Route::get('/export')->uses('ExportController@index')->name('admin.export.index');
Route::post('/export/{destination}/show')->uses('ExportController@show')->name('admin.export.show');
Route::post('/export/{destination}/create')->uses('ExportController@create')->name('admin.export.create');
Route::delete('/export/{id}/delete')->uses('ExportController@delete')->name('admin.export.delete');