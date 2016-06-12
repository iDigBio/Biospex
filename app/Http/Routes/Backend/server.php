<?php
$router->get('server', [
    'uses' => 'ServerController@index',
    'as'   => 'admin.server.index'
]);

$router->get('server/show', [
    'uses' => 'ServerController@show',
    'as'   => 'admin.server.show'
]);