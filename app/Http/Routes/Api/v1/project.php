<?php

// Projects
$api->post('projects', ['as' => 'api.projects.create', 'uses' => 'ProjectsController@create']);
$api->get('projects/{uuid}', ['as' => 'api.projects.show', 'uses' => 'ProjectsController@show']);
$api->put('projects/{uuid}', ['as' => 'api.projects.update', 'uses' => 'ProjectsController@update']);
$api->delete('projects/{uuid}', ['as' => 'api.projects.delete', 'uses' => 'ProjectsController@delete']);
$api->get('projects', ['as' => 'api.projects.index', 'uses' => 'ProjectsController@index']);