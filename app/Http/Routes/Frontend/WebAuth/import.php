<?php

// Begin Import Controller
$router->get('projects/{projects}/import', [
    'uses' => 'ImportsController@import',
    'as'   => 'web.imports.import'
]);

$router->post('projects/{projects}/import', [
    'uses' => 'ImportsController@upload',
    'as'   => 'web.imports.upload'
]);
// End Import Controller