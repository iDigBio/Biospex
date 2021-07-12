<?php

Route::get('/download/product/{file}')->uses('DownloadController@product')->name('front.download.product');


