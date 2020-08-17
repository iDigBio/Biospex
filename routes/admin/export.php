<?php

use Illuminate\Support\Facades\Route;

Route::get('/export')->uses('ExportController@index')->name('admin.get.export');