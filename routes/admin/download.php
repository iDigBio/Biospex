<?php

use Illuminate\Support\Facades\Route;

Route::get('/download/report/{file}')->uses('DownloadController@report')->name('admin.download.report');
Route::get('/download/export/{file}')->uses('DownloadController@export')->name('admin.download.export');
Route::get('/download/version/{file}')->uses('DownloadController@version')->name('admin.download.version');
Route::get('/download/product/{file}')->uses('DownloadController@product')->name('admin.download.product');