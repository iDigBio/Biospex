<?php

$router->get('ocr', [
    'uses' => 'OcrController@index',
    'as'   => 'ocr.get.index'
]);

$router->post('ocr', [
    'uses' => 'OcrController@index',
    'as'   => 'ocr.post.index'
]);