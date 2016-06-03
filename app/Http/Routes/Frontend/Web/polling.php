<?php

// Ajax poll event
$router->get('poll', [
    'uses' => 'ServerController@pollOcr',
    'as'   => 'server.get.pollOcr'
]);