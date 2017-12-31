<?php

$router->post('/panoptes-pusher', [
    'as' => 'api.panoptes-pusher.create',
    'uses' => 'PanoptesPusherController@create',
]);