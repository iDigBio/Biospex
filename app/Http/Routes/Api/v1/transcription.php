<?php

$api->post('transcriptions', ['as' => 'api.transcriptions.create', 'uses' => 'TranscriptionsController@create']);
$api->post('transcriptions/{uuid}', ['as' => 'api.transcriptions.show', 'uses' => 'TranscriptionsController@show']);
$api->put('transcriptions/{uuid}', ['as' => 'api.transcriptions.update', 'uses' => 'TranscriptionsController@update']);
$api->delete('transcriptions/{uuid}', ['as' => 'api.transcriptions.delete', 'uses' => 'TranscriptionsController@delete']);
$api->get('transcriptions', ['as' => 'api.transcriptions.index', 'uses' => 'TranscriptionsController@index']);