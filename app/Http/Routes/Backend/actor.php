<?php

// Begin ActorsController
$router->get('actors', [
    'uses' => 'ActorsController@index',
    'as'   => 'admin.actors.index'
]);

$router->get('actors/create', [
    'uses' => 'ActorsController@create',
    'as'   => 'admin.actors.create'
]);

$router->post('actors/create', [
    'uses' => 'ActorsController@store',
    'as'   => 'admin.actors.store'
]);

$router->get('actors/{actors}', [
    'uses' => 'ActorsController@show',
    'as'   => 'admin.actors.show'
]);

$router->get('actors/{actors}/edit', [
    'uses' => 'ActorsController@edit',
    'as'   => 'admin.actors.edit'
]);

$router->put('actors/{actors}', [
    'uses' => 'ActorsController@update',
    'as'   => 'admin.actors.update'
]);

$router->delete('actors/{actors}', [
    'uses' => 'ActorsController@delete',
    'as'   => 'admin.actors.delete'
]);

$router->delete('actors/{actors}/trash', [
    'uses' => 'ActorsController@trash',
    'as'   => 'admin.actors.trash'
]);

// End ActorsController
