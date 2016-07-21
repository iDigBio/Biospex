<?php

// Begin Pages
$router->get('pages', [
    'uses' => 'PagesController@index',
    'as'   => 'admin.pages.index'
]);

$router->get('pages/create', [
    'uses' => 'PagesController@create',
    'as'   => 'admin.pages.create'
]);

$router->post('pages/create', [
    'uses' => 'PagesController@store',
    'as'   => 'admin.pages.store'
]);

$router->get('pages/{pages}', [
    'uses' => 'PagesController@show',
    'as'   => 'admin.pages.show'
]);

$router->get('pages/{pages}/edit', [
    'uses' => 'PagesController@edit',
    'as'   => 'admin.pages.edit'
]);

$router->put('pages/{pages}', [
    'uses' => 'PagesController@update',
    'as'   => 'admin.pages.update'
]);

$router->delete('pages/{pages}', [
    'uses' => 'PagesController@delete',
    'as'   => 'admin.pages.delete'
]);

$router->delete('pages/{pages}/trash', [
    'uses' => 'PagesController@trash',
    'as'   => 'admin.pages.trash'
]);
// End Pages