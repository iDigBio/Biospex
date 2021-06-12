<?php

use Illuminate\Support\Facades\Route;

Route::get('/product')->uses('ProductController@index')->name('admin.product.index');
Route::post('/product')->uses('ProductController@create')->name('admin.product.create');