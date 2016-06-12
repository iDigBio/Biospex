<?php

// Groups
$api->post('groups', ['as' => 'api.groups.create', 'uses' =>  'GroupsController@create']);
$api->get('groups/{uuid}', ['as' => 'api.groups.show', 'uses' =>  'GroupsController@show']);
$api->put('groups/{uuid}', ['as' => 'api.groups.update', 'uses' =>  'GroupsController@update']);
$api->delete('groups/{uuid}', ['as' => 'api.groups.delete', 'uses' =>  'GroupsController@delete']);
$api->get('groups', ['as' => 'api.groups.index', 'uses' =>  'GroupsController@index']);