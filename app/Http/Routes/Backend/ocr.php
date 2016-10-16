<?php
$router->get('ocr', [
    'uses' => 'OcrController@index',
    'as'   => 'admin.ocr.index'
]);

$router->get('ocr/create', [
    'uses' => 'OcrController@create',
    'as'   => 'ocr.ocr.create'
]);

$router->post('ocr/create', [
    'uses' => 'OcrController@store',
    'as'   => 'admin.ocr.store'
]);

$router->get('ocr/{ocr}', [
    'uses' => 'OcrController@show',
    'as'   => 'admin.ocr.show'
]);

$router->get('ocr/{ocr}/edit', [
    'uses' => 'OcrController@edit',
    'as'   => 'admin.ocr.edit'
]);

$router->put('ocr/{ocr}', [
    'uses' => 'OcrController@update',
    'as'   => 'admin.ocr.update'
]);

$router->delete('ocr/delete/{ocr?}', [
    'uses' => 'OcrController@delete',
    'as'   => 'admin.ocr.delete'
]);