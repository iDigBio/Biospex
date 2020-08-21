<?php

use Illuminate\Support\Facades\Route;

Route::get('grids/load', ['as' => 'admin.grids.load', 'uses' => 'GridsController@load']);
Route::get('grids/read', ['as' => 'admin.grids.read', 'uses' => 'GridsController@read']);
