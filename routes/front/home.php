<?php

use Illuminate\Support\Facades\Route;

Route::get('/')->uses('IndexController@index')->name('home');
Route::get('/record/{id}/data/{view?}')->uses('IndexController@data')->name('front.data.get');
Route::get('/record/{id}/{view?}')->uses('IndexController@show')->name('front.show.get');

