<?php
$router->get('server', [
    'uses' => 'ServerController@index',
    'as'   => 'server.get.index'
]);