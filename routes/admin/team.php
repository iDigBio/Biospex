<?php

// Index
$router->get('teams')->uses('TeamsController@index')->name('admin.teams.index');

// Begin Teams
$router->get('teams/{categories}')->uses('TeamsController@create')->name('admin.teams.create');
$router->post('teams/{categories?}')->uses('TeamsController@store')->name('admin.teams.store');
$router->get('teams/{categories}/{teams}')->uses('TeamsController@edit')->name('admin.teams.edit');
$router->put('teams/{categories}/{teams}')->uses('TeamsController@update')->name('admin.teams.update');
$router->delete('teams/{categories}/{teams}')->uses('TeamsController@delete')->name('admin.teams.delete');

// Begin Categories
$router->get('teams/{categories}/{teams}/categories')->uses('TeamsController@editCategory')->name('admin.teams.categories.edit');
$router->put('teams/{categories}/{teams}/categories')->uses('TeamsController@updateCategory')->name('admin.teams.categories.update');
$router->post('teams/create/category')->uses('TeamsController@storeCategory')->name('admin.teams.category.store');
