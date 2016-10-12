<?php

// Ajax poll event
$router->get('poll', [
    'uses' => 'ServerController@poll',
    'as'   => 'server.get.poll'
]);