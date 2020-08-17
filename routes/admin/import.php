<?php

use Illuminate\Support\Facades\Route;

Route::get('/import')->uses('ImportController@index')->name('admin.get.import');