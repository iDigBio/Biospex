<?php

$router->get('ocr')->uses('OcrController@index')->name('admin.ocr.index');
$router->get('ocr/create')->uses('OcrController@create')->name('ocr.ocr.create');
$router->post('ocr/create')->uses('OcrController@store')->name('admin.ocr.store');
$router->get('ocr/{ocr}')->uses('OcrController@show')->name('admin.ocr.show');
$router->get('ocr/{ocr}/edit')->uses('OcrController@edit')->name('admin.ocr.edit');
$router->put('ocr/{ocr}')->uses('OcrController@update')->name('admin.ocr.update');
$router->delete('ocr/delete/{ocr?}')->uses('OcrController@delete')->name('admin.ocr.delete');