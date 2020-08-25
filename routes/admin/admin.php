<?php

use Illuminate\Support\Facades\Route;

Route::get('/')->uses('DashboardController@index')->name('admin.get.index');