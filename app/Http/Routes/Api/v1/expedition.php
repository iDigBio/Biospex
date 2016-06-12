<?php

// Expeditions
$api->post('expeditions', ['as' => 'api.expeditions.create', 'uses' =>'ExpeditionsController@create']);
$api->get('expeditions/{uuid}', ['as' => 'api.expeditions.show', 'uses' =>'ExpeditionsController@show']);
$api->put('expeditions/{uuid}', ['as' => 'api.expeditions.update', 'uses' =>'ExpeditionsController@update']);
$api->delete('expeditions/{uuid}', ['as' => 'api.expeditions.delete', 'uses' =>'ExpeditionsController@delete']);
$api->get('expeditions', ['as' => 'api.expeditions.index', 'uses' =>'ExpeditionsController@index']);
