<?php

// Groups
$api->post('groups', ['as' => 'api.groups.create', 'uses' =>  'GoupsController@create']);
$api->get('groups/{uuid}', ['as' => 'api.groups.show', 'uses' =>  'GoupsController@show']);
$api->put('groups/{uuid}', ['as' => 'api.groups.update', 'uses' =>  'GoupsController@update']);
$api->delete('groups/{uuid}', ['as' => 'api.groups.delete', 'uses' =>  'GoupsController@delete']);
$api->get('groups', ['as' => 'api.groups.index', 'uses' =>  'GoupsController@index']);