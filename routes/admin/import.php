<?php

// Begin Import Controller
$router->get('projects/{projects}/import')->uses('ImportsController@index')->name('admin.imports.index');
$router->post('projects/{projects}/dwcfile')->uses('ImportsController@dwcFile')->name('admin.imports.dwcfile');
$router->post('projects/{projects}/recordset')->uses('ImportsController@recordSet')->name('admin.imports.recordset');
$router->post('projects/{projects}/dwcuri')->uses('ImportsController@dwcUri')->name('admin.imports.dwcuri');