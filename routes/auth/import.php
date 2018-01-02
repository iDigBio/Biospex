<?php

// Begin Import Controller
$router->get('projects/{projects}/import')->uses('ImportsController@import')->name('web.imports.import');
$router->post('projects/{projects}/dwcfile')->uses('ImportsController@uploadDwcFile')->name('web.dwcfile.upload');
$router->post('projects/{projects}/recordset')->uses('ImportsController@uploadRecordSet')->name('web.recordset.upload');
$router->post('projects/{projects}/dwcuri')->uses('ImportsController@uploadDwcUri')->name('web.dwcuri.upload');