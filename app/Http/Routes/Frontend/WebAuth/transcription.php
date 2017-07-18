<?php

$router->get('projects/{projects}/expeditions/{expeditions}/transcriptions', [
    'uses' => 'TranscriptionsController@index',
    'as'   => 'web.transcriptions.index'
]);
