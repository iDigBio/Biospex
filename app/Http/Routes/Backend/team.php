<?php

// Index
$router->get('teams', [
    'uses' => 'TeamsController@index',
    'as'   => 'admin.teams.index'
]);

// Begin Teams
$router->get('teams/{categories}', [
    'uses' => 'TeamsController@create',
    'as'   => 'admin.teams.create'
]);

$router->post('teams/{categories?}', [
    'uses' => 'TeamsController@store',
    'as'   => 'admin.teams.store'
]);

$router->get('teams/{categories}/{teams}', [
    'uses' => 'TeamsController@edit',
    'as'   => 'admin.teams.edit'
]);

$router->put('teams/{categories}/{teams}', [
    'uses' => 'TeamsController@update',
    'as'   => 'admin.teams.update'
]);


$router->delete('teams/{categories}/{teams}', [
    'uses' => 'TeamsController@delete',
    'as'   => 'admin.teams.delete'
]);
// End Teams


// Begin Categories
$router->get('teams/{categories}/{teams}/categories', [
    'uses' => 'TeamsController@editCategory',
    'as'   => 'admin.teams.categories.edit'
]);

$router->put('teams/{categories}/{teams}/categories', [
    'uses' => 'TeamsController@updateCategory',
    'as'   => 'admin.teams.categories.update'
]);

$router->post('teams/create/category', [
    'uses' => 'TeamsController@storeCategory',
    'as'   => 'admin.teams.category.store'
]);
// End Categories