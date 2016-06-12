<?php

// Users
$api->post('users', ['as' => 'api.users.create', 'uses' => 'UsersController@create']);
$api->get('users/{uuid}', ['as' => 'api.users.show', 'uses' => 'UsersController@show']);
$api->put('users/{uuid}', ['as' => 'api.users.update', 'uses' => 'UsersController@update']);
$api->delete('users/{uuid}', ['as' => 'api.users.delete', 'uses' => 'UsersController@delete']);
$api->get('users', ['as' => 'api.users.index', 'uses' => 'UsersController@index']);
