<?php

// Begin Import Controller
$router->get('projects/{projects}/import')->uses('ImportsController@import')->name('admin.imports.import');
$router->post('projects/{projects}/dwcfile')->uses('ImportsController@uploadDwcFile')->name('admin.imports.dwcfile.upload');
$router->post('projects/{projects}/recordset')->uses('ImportsController@uploadRecordSet')->name('admin.imports.recordset.upload');
$router->post('projects/{projects}/dwcuri')->uses('ImportsController@uploadDwcUri')->name('admin.imports.dwcuri.upload');