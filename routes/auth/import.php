<?php

// Begin Import Controller
$router->get('projects/{projects}/import')->uses('ImportsController@import')->name('web.imports.import');
$router->post('projects/{projects}/import')->uses('ImportsController@upload')->name('web.imports.upload');