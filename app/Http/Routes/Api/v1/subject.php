<?php

// Subjects
$api->post('subjects', ['as' => 'api.subjects.create', 'uses' => 'SubjectsController@create']);
$api->get('subjects/{uuid}', ['as' => 'api.subjects.show', 'uses' => 'SubjectsController@show']);
$api->put('subjects/{uuid}', ['as' => 'api.subjects.update', 'uses' => 'SubjectsController@update']);
$api->delete('subjects/{uuid}', ['as' => 'api.subjects.delete', 'uses' => 'SubjectsController@delete']);
$api->get('subjects', ['as' => 'api.subjects.index', 'uses' => 'SubjectsController@index']);
