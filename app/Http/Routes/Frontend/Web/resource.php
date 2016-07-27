<?php

// Begin Resource
$router->get('resource', [
    'uses' => 'ResourcesController@index',
    'as'   => 'web.resources.index'
]);

$router->get('resource/{id}', [
    'uses' => 'ResourcesController@download',
    'as'   => 'web.resources.download'
]);
// End Resource