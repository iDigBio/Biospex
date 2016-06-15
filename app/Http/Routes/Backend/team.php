<?php
$router->get('team', [
    'uses' => 'TeamsController@index',
    'as'   => 'admin.teams.index'
]);

$router->get('team/create/{categories?}', [
    'uses' => 'TeamsController@create',
    'as'   => 'admin.teams.create'
]);

$router->post('team/create', [
    'uses' => 'TeamsController@store',
    'as'   => 'admin.teams.store'
]);

$router->post('team/createCategory', [
    'uses' => 'TeamsController@storeCategory',
    'as'   => 'admin.teams.category.store'
]);

$router->get('team/{teams}', [
    'uses' => 'TeamsController@show',
    'as'   => 'admin.teams.show'
]);

$router->get('team/{categories}/{teams?}/edit', [
    'uses' => 'TeamsController@edit',
    'as'   => 'admin.teams.edit'
]);

$router->put('team/{categories}/{teams}', [
    'uses' => 'TeamsController@update',
    'as'   => 'admin.teams.update'
]);

$router->put('team/{categories}', [
    'uses' => 'TeamsController@updateCategory',
    'as'   => 'admin.teams.categories.update'
]);

$router->delete('team/{categories}/{teams?}/edit', [
    'uses' => 'TeamsController@delete',
    'as'   => 'admin.teams.delete'
]);