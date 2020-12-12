<?php

use Illuminate\Support\Facades\Route;

Route::get('/ingest')->uses('IngestController@index')->name('admin.ingest.index');
Route::post('/ingest/import')->uses('IngestController@create')->name('admin.ingest.create');
Route::put('/ingest/update')->uses('IngestController@update')->name('admin.ingest.update');
