<?php

// Begin Import Controller
$router->get('projects/{projects}/import', [
    'uses' => 'ImportsController@import',
    'as'   => 'projects.get.import'
]);

$router->post('projects/{projects}/import', [
    'uses' => 'ImportsController@upload',
    'as'   => 'projects.post.upload'
]);
// End Import Controller