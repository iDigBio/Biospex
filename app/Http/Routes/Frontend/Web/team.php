<?php

// Begin About
$router->get('team', [
    'uses' => 'TeamsController@index',
    'as'   => 'web.teams.index'
]);
// End Team