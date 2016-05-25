<?php

// Api home page
$api->get('/', ['as' => 'api.index', 'uses' => 'ApiController@index']);

// Users
$api->post('users', ['as' => 'api.users.create', 'uses' => 'UsersController@create']);
$api->get('users/{uuid}', ['as' => 'api.users.show', 'uses' => 'UsersController@show']);
$api->put('users/{uuid}', ['as' => 'api.users.update', 'uses' => 'UsersController@update']);
$api->delete('users/{uuid}', ['as' => 'api.users.delete', 'uses' => 'UsersController@delete']);
$api->get('users', ['as' => 'api.users.index', 'uses' => 'UsersController@index']);

// Groups
$api->post('groups', ['as' => 'api.groups.create', 'uses' =>  'GoupsController@create']);
$api->get('groups/{uuid}', ['as' => 'api.groups.show', 'uses' =>  'GoupsController@show']);
$api->put('groups/{uuid}', ['as' => 'api.groups.update', 'uses' =>  'GoupsController@update']);
$api->delete('groups/{uuid}', ['as' => 'api.groups.delete', 'uses' =>  'GoupsController@delete']);
$api->get('groups', ['as' => 'api.groups.index', 'uses' =>  'GoupsController@index']);

// Projects
$api->post('projects', ['as' => 'api.projects.create', 'uses' => 'ProjectsController@create']);
$api->get('projects/{uuid}', ['as' => 'api.projects.show', 'uses' => 'ProjectsController@show']);
$api->put('projects/{uuid}', ['as' => 'api.projects.update', 'uses' => 'ProjectsController@update']);
$api->delete('projects/{uuid}', ['as' => 'api.projects.delete', 'uses' => 'ProjectsController@delete']);
$api->get('projects', ['as' => 'api.projects.index', 'uses' => 'ProjectsController@index']);

// Expeditions
$api->post('expeditions', ['as' => 'api.expeditions.create', 'uses' =>'ExpeditionsController@create']);
$api->get('expeditions/{uuid}', ['as' => 'api.expeditions.show', 'uses' =>'ExpeditionsController@show']);
$api->put('expeditions/{uuid}', ['as' => 'api.expeditions.update', 'uses' =>'ExpeditionsController@update']);
$api->delete('expeditions/{uuid}', ['as' => 'api.expeditions.delete', 'uses' =>'ExpeditionsController@delete']);
$api->get('expeditions', ['as' => 'api.expeditions.index', 'uses' =>'ExpeditionsController@index']);

// Subjects
$api->post('subjects', ['as' => 'api.subjects.create', 'uses' => 'SubjectsController@create']);
$api->get('subjects/{uuid}', ['as' => 'api.subjects.show', 'uses' => 'SubjectsController@show']);
$api->put('subjects/{uuid}', ['as' => 'api.subjects.update', 'uses' => 'SubjectsController@update']);
$api->delete('subjects/{uuid}', ['as' => 'api.subjects.delete', 'uses' => 'SubjectsController@delete']);
$api->get('subjects', ['as' => 'api.subjects.index', 'uses' => 'SubjectsController@index']);

// Transcriptions
$api->post('transcriptions', ['as' => 'api.transcriptions.create', 'uses' => 'TranscriptionsController@create']);
$api->post('transcriptions/{uuid}', ['as' => 'api.transcriptions.show', 'uses' => 'TranscriptionsController@show']);
$api->put('transcriptions/{uuid}', ['as' => 'api.transcriptions.update', 'uses' => 'TranscriptionsController@update']);
$api->delete('transcriptions/{uuid}', ['as' => 'api.transcriptions.delete', 'uses' => 'TranscriptionsController@delete']);
$api->get('transcriptions', ['as' => 'api.transcriptions.index', 'uses' => 'TranscriptionsController@index']);
