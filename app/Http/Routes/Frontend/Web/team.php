<?php

// Begin Team
$router->get('team', [
    'uses' => 'TeamsController@index',
    'as'   => 'web.teams.index'
]);
// End Team