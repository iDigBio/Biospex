<?php

// Project/Grid
$router->get('grids/{projects}/load', ['as' => 'admin.grids.load', 'uses' => 'GridsController@load']);
$router->get('grids/{projects}/expeditions/{expeditions}/edit', ['as' => 'admin.grids.edit', 'uses' => 'GridsController@expeditionsEdit']);
