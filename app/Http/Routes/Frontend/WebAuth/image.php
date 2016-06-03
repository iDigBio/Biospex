<?php

// ImagesController
$router->get('images/html', [
    'uses' => 'ImagesController@html',
    'as'   => 'images.html'
]);

$router->get('images/preview', [
    'uses' => 'ImagesController@preview',
    'as'   => 'images.preview'
]);
