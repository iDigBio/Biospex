<?php

// Begin Import Controller
$router->get('projects/{projects}/import')->uses('ImportsController@import')->name('webauth.imports.import');
$router->post('projects/{projects}/dwcfile')->uses('ImportsController@uploadDwcFile')->name('webauth.imports.dwcfile.upload');
$router->post('projects/{projects}/recordset')->uses('ImportsController@uploadRecordSet')->name('webauth.imports.recordset.upload');
$router->post('projects/{projects}/dwcuri')->uses('ImportsController@uploadDwcUri')->name('webauth.imports.dwcuri.upload');