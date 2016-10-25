<?php

// ImagesController
$router->get('img/html', [
    'uses' => 'ImagesController@html',
    'as'   => 'images.html'
]);

$router->get('img/preview', [
    'uses' => 'ImagesController@preview',
    'as'   => 'images.preview'
]);
