<?php

use Illuminate\Support\Facades\Route;

Route::get('/')->uses('IndexController@index')->name('home');
Route::get('/data/{id}/{view?}')->uses('IndexController@data')->name('front.data.get');
Route::get('/record/{view}/{id?}')->uses('IndexController@show')->name('front.show.get');

