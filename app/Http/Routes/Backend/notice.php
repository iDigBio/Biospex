<?php

// Begin NoticesController
$router->get('notices', [
    'uses' => 'NoticesController@index',
    'as'   => 'admin.notices.index'
]);

$router->get('notices/create', [
    'uses' => 'NoticesController@create',
    'as'   => 'admin.notices.create'
]);

$router->post('notices/create', [
    'uses' => 'NoticesController@store',
    'as'   => 'admin.notices.store'
]);

$router->get('notices/{notices}', [
    'uses' => 'NoticesController@show',
    'as'   => 'admin.notices.show'
]);

$router->get('notices/{notices}/edit', [
    'uses' => 'NoticesController@edit',
    'as'   => 'admin.notices.edit'
]);

$router->put('notices/{notices}', [
    'uses' => 'NoticesController@update',
    'as'   => 'admin.notices.update'
]);

$router->delete('notices/{notices}', [
    'uses' => 'NoticesController@delete',
    'as'   => 'admin.notices.delete'
]);

$router->delete('notices/{notices}/trash', [
    'uses' => 'NoticesController@trash',
    'as'   => 'admin.notices.trash'
]);

$router->get('notices/{notices}/enable', [
    'uses' => 'NoticesController@enable',
    'as'   => 'admin.notices.enable'
]);

$router->get('notices/{notices}/disable', [
    'uses' => 'NoticesController@disable',
    'as'   => 'admin.notices.disable'
]);