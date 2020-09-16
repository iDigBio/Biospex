<?php

use Illuminate\Support\Facades\Route;

Route::get('/download/report/{fileName}')->uses('DownloadController@report')->name('admin.download.report');
Route::get('/download/export/{fileName}')->uses('DownloadController@export')->name('admin.download.export');