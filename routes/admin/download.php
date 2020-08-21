<?php

use Illuminate\Support\Facades\Route;

Route::get('/download/report/{fileName}')->uses('DownloadController@report')->name('admin.download.report');