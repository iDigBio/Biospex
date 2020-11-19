<?php

use Illuminate\Support\Facades\Route;

Route::get('grids/read', ['as' => 'admin.grids.read', 'uses' => 'GridsController@read']);
