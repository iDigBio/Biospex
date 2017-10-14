<?php

// Ajax poll event
$router->post('panoptes-pusher', [
    'uses' => 'ServerController@panoptesPusher',
    'as'   => 'server.post.panoptes-pusher'
]);